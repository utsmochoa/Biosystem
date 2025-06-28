import os
import sys
import json
import threading
import time
from ctypes import *
from tkinter import *
from tkinter import ttk
from tkinter import messagebox
from PIL import Image, ImageTk
import mysql.connector
from mysql.connector import Error

# ------------------------------------------------------------------------------ 
# 1) Cargar la DLL del SDK con RTLD_GLOBAL 
# ------------------------------------------------------------------------------ 
ftrdll = CDLL('ftrapi.dll', mode=RTLD_GLOBAL)

# ------------------------------------------------------------------------------ 
# 2) Definición de constantes según ftrapi.h 
# ------------------------------------------------------------------------------ 
FTR_PARAM_IMAGE_WIDTH           = c_ulong(1) 
FTR_PARAM_IMAGE_HEIGHT          = c_ulong(2) 
FTR_PARAM_IMAGE_SIZE            = c_ulong(3) 
FTR_PARAM_CB_FRAME_SOURCE       = c_ulong(4) 
FTR_PARAM_CB_CONTROL            = c_ulong(5) 
FTR_PARAM_MAX_TEMPLATE_SIZE     = c_ulong(6) 
FTR_PARAM_MAX_FAR_REQUESTED     = c_ulong(7) 
FTR_PARAM_SYS_ERROR_CODE        = c_ulong(8) 
FTR_PARAM_FAKE_DETECT           = c_ulong(9) 
FTR_PARAM_MAX_MODELS            = c_ulong(10) 
FTR_PARAM_FFD_CONTROL           = c_ulong(11) 
FTR_PARAM_MIOT_CONTROL          = c_ulong(12) 
FTR_PARAM_MAX_FARN_REQUESTED    = c_ulong(13) 
FTR_PARAM_VERSION               = c_ulong(14)

FSD_FUTRONIC_USB    = c_void_p(1)

FTR_CB_RESP_CANCEL      = c_ulong(1) 
FTR_CB_RESP_CONTINUE    = c_ulong(2)

FTR_PURPOSE_IDENTIFY = c_ulong(2) 
FTR_PURPOSE_ENROLL   = c_ulong(3) 
FTR_PURPOSE_COMPATIBILITY = c_ulong(4)

FTR_STATE_FRAME_PROVIDED    = 0x01
FTR_STATE_SIGNAL_PROVIDED   = 0x02

FTR_VERSION_CURRENT = c_ulong(3)

# Códigos de retorno según la documentación
FTR_RETCODE_OK = 0
FTR_RETCODE_INVALID_ARG = 1
FTR_RETCODE_INVALID_PURPOSE = 2

# ------------------------------------------------------------------------------ 
# 3) Definición de estructuras (con alineación a 1 byte) 
# ------------------------------------------------------------------------------ 
class FtrData(Structure): 
    _pack_ = 1
    _fields_ = [ 
        ('dwsize', c_ulong), 
        ('pdata', c_void_p) 
    ]

class FtrBitmap(Structure): 
    _pack_ = 1 
    _fields_ = [ 
        ('width', c_ulong), 
        ('height', c_ulong), 
        ('bitmap', FtrData) 
    ]

class FtrEnrollData(Structure): 
    _pack_ = 1 
    _fields_ = [ 
        ('dwsize', c_ulong), 
        ('dwquality', c_ulong) 
    ]

# ------------------------------------------------------------------------------ 
# 5) Callback de control 
# ------------------------------------------------------------------------------ 
@CFUNCTYPE(c_void_p, c_void_p, c_ulong, c_void_p, c_ulong, c_void_p) 
def cbControl(context, state, response, signal, bitmap): 
    cast(response, POINTER(c_ulong)).contents.value = FTR_CB_RESP_CONTINUE.value 
    return None

# ------------------------------------------------------------------------------
# 4) Funciones de base de datos
# ------------------------------------------------------------------------------

def conectar_bd():
    try:
        return mysql.connector.connect(
            host='localhost',
            user='BioSystem',
            password='huella2128',
            database='biosystem'
        )
    except Error as e:
        return None

def obtener_ultimo_estudiante():
    conexion = conectar_bd()
    if conexion:
        try:
            cursor = conexion.cursor()
            query = "SELECT id FROM estudiantes ORDER BY id DESC LIMIT 1"
            cursor.execute(query)
            estudiante = cursor.fetchone()
            cursor.close()
            conexion.close()
            return estudiante[0] if estudiante else None
        except Error as e:
            return None
    return None

def obtener_estudiante_por_id(estudiante_id):
    """
    Obtiene un estudiante específico por su ID
    """
    conexion = conectar_bd()
    if conexion:
        try:
            cursor = conexion.cursor()
            query = "SELECT id, nombres, apellidos, cedula_identidad FROM estudiantes WHERE id = %s"
            cursor.execute(query, (estudiante_id,))
            estudiante = cursor.fetchone()
            cursor.close()
            conexion.close()
            return estudiante
        except Error as e:
            return None
    return None

def verificar_huella_duplicada_mejorado(template_bytes, estudiante_actual_id):
    """
    Verifica si la huella capturada ya existe en la base de datos usando FTRVerifyN
    """
    conexion = conectar_bd()
    if not conexion:
        print("Error: No se pudo conectar a la base de datos para verificar duplicados")
        return False, None
    
    try:
        cursor = conexion.cursor()
        # Obtener todas las huellas existentes excepto las del estudiante actual
        query = "SELECT estudiante_id, huella_data FROM huellas_digitales WHERE estudiante_id != %s AND activo = 1"
        cursor.execute(query, (estudiante_actual_id,))
        huellas_existentes = cursor.fetchall()
        cursor.close()
        conexion.close()
        
        print(f"Verificando contra {len(huellas_existentes)} huellas existentes...")
        
        if not huellas_existentes:
            print("No hay huellas existentes para comparar")
            return False, None
        
        # Usar el SDK para comparar
        return comparar_huellas_con_sdk(template_bytes, huellas_existentes)
        
    except Error as e:
        print(f"Error al verificar duplicados: {e}")
        return False, None

def comparar_huellas_con_sdk(template_bytes, huellas_existentes):
    """
    Compara usando FTRVerifyN del SDK
    """
    try:
        # Configurar FAR más estricto para evitar falsos positivos
        ftrdll.FTRSetParam(FTR_PARAM_MAX_FARN_REQUESTED, c_ulong(100))
        
        # Crear estructura para la nueva huella
        nueva_huella = FtrData()
        nueva_huella.dwsize = len(template_bytes)
        buf_nueva = create_string_buffer(template_bytes)
        nueva_huella.pdata = cast(buf_nueva, c_void_p)
        
        for i, (estudiante_id, huella_existente) in enumerate(huellas_existentes):
            print(f"Comparando con huella {i+1}/{len(huellas_existentes)} del estudiante {estudiante_id}")
            
            if not huella_existente or len(huella_existente) < 10:
                continue
            
            try:
                # Crear estructura para la huella existente
                huella_bd = FtrData()
                huella_bd.dwsize = len(huella_existente)
                buf_bd = create_string_buffer(huella_existente)
                huella_bd.pdata = cast(buf_bd, c_void_p)
                
                # Variables para el resultado
                result = c_long(0)
                ftr_farn = c_ulong(0)
                
                # Usar FTRVerifyN para comparar
                res = ftrdll.FTRVerifyN(
                    None, 
                    byref(huella_bd), 
                    byref(result), 
                    byref(ftr_farn)
                )
                
                if res == FTR_RETCODE_OK:
                    if result.value == 1:  # Match found
                        print(f"¡DUPLICADO ENCONTRADO! Estudiante {estudiante_id}, FARN: {ftr_farn.value}")
                        return True, estudiante_id
                    else:
                        print(f"No coincide con estudiante {estudiante_id}, FARN: {ftr_farn.value}")
                else:
                    print(f"FTRVerifyN falló con código: {res} para estudiante {estudiante_id}")
                    
            except Exception as e:
                print(f"Error comparando huella del estudiante {estudiante_id}: {e}")
                continue
                
        print("No se encontraron duplicados")
        return False, None
        
    except Exception as e:
        print(f"Error en comparación con SDK: {e}")
        return False, None

def obtener_datos_estudiante(estudiante_id):
    """
    Obtiene los datos básicos de un estudiante por su ID
    """
    conexion = conectar_bd()
    if not conexion:
        return None
    
    try:
        cursor = conexion.cursor()
        query = "SELECT nombre, apellido, cedula FROM estudiantes WHERE id = %s"
        cursor.execute(query, (estudiante_id,))
        estudiante = cursor.fetchone()
        cursor.close()
        conexion.close()
        
        if estudiante:
            return {
                'nombre': estudiante[0],
                'apellido': estudiante[1],
                'cedula': estudiante[2]
            }
        return None
        
    except Error as e:
        return None

def guardar_huella(estudiante_id, template_bytes, calidad):
    conexion = conectar_bd()
    if not conexion:
        return False
    try:
        cursor = conexion.cursor()
        query = "INSERT INTO huellas_digitales (estudiante_id, huella_data, quality) VALUES (%s, %s, %s)"
        cursor.execute(query, (estudiante_id, template_bytes, calidad))
        conexion.commit()
        return True
    except Error as e:
        return False
    finally:
        cursor.close()
        conexion.close()

class FingerprintEnrollGUI:
    def __init__(self, estudiante_id=None):
        self.estudiante_id = estudiante_id
        self.estudiante_data = None
        
        # Si se proporcionó un ID, obtener los datos del estudiante
        if self.estudiante_id:
            self.estudiante_data = obtener_estudiante_por_id(self.estudiante_id)
            if not self.estudiante_data:
                result = {
                    "success": False,
                    "message": f"No se encontró el estudiante con ID {self.estudiante_id}"
                }
                print(json.dumps(result))
                sys.exit(1)
        
        self.root = Tk()
        self.setup_window()
        self.create_widgets()
        self.countdown_active = False
        self.capturing = False
        
        # Iniciar countdown automáticamente después de crear la ventana
        self.root.after(500, self.start_countdown)
        
    def setup_window(self):
        self.root.title("Registrar Huella Digital")
        self.root.geometry("400x350")
        self.root.resizable(False, False)
        
        # Hacer que la ventana esté siempre al frente
        self.root.attributes('-topmost', True)
        
        # Centrar la ventana
        self.root.eval('tk::PlaceWindow . center')
        
        # Configurar el cierre de la ventana
        self.root.protocol("WM_DELETE_WINDOW", self.on_closing)
        
        # Configurar estilo
        self.root.configure(bg='#f0f0f0')
        
    def create_widgets(self):
        # Frame principal
        main_frame = Frame(self.root, bg='#f0f0f0', padx=20, pady=20)
        main_frame.pack(fill=BOTH, expand=True)
        
        # Título
        title_label = Label(main_frame, 
                           text="Registro de Huella Digital", 
                           font=('Arial', 14, 'bold'),
                           bg='#f0f0f0',
                           fg='#2c3e50')
        title_label.pack(pady=(0, 10))
        
        # Información del estudiante
        if self.estudiante_data:
            info_text = f"Estudiante: {self.estudiante_data[1]} {self.estudiante_data[2]}\nCédula: {self.estudiante_data[3]}"
        else:
            info_text = "Nuevo estudiante"
            
        info_label = Label(main_frame, 
                          text=info_text, 
                          font=('Arial', 10),
                          bg='#f0f0f0',
                          fg='#7f8c8d',
                          justify=CENTER)
        info_label.pack(pady=(0, 20))
        
        # Ícono de huella
        icon_label = Label(main_frame, 
                          text="👆", 
                          font=('Arial', 48),
                          bg='#f0f0f0')
        icon_label.pack(pady=(0, 20))
        
        # Etiqueta de estado
        self.status_label = Label(main_frame, 
                                 text="Preparándose para capturar...", 
                                 font=('Arial', 11),
                                 bg='#f0f0f0',
                                 fg='#34495e')
        self.status_label.pack(pady=(0, 15))
        
        # Etiqueta de countdown
        self.countdown_label = Label(main_frame, 
                                   text="", 
                                   font=('Arial', 32, 'bold'),
                                   bg='#f0f0f0',
                                   fg='#e74c3c')
        self.countdown_label.pack(pady=(0, 30))
        
        # Botón Cancelar
        self.cancel_button = Button(main_frame, 
                                   text="Cancelar", 
                                   command=self.cancel_operation,
                                   bg='#e74c3c',
                                   fg='white',
                                   font=('Arial', 10, 'bold'),
                                   padx=30,
                                   pady=8,
                                   relief=FLAT,
                                   cursor='hand2')
        self.cancel_button.pack()
        
    def start_countdown(self):
        if self.countdown_active or self.capturing:
            return
            
        self.countdown_active = True
        self.status_label.config(text="Preparándose para capturar...")
        
        # Iniciar countdown en un hilo separado
        threading.Thread(target=self.countdown_thread, daemon=True).start()
        
    def countdown_thread(self):
        # Countdown de 2 segundos
        for i in range(2, 0, -1):
            if not self.countdown_active:
                return
            self.root.after(0, lambda count=i: self.countdown_label.config(text=str(count)))
            time.sleep(1)
            
        if self.countdown_active:
            self.root.after(0, lambda: self.countdown_label.config(text="¡CAPTURANDO!"))
            self.root.after(0, self.start_capture)
            
    def start_capture(self):
        self.countdown_active = False
        self.capturing = True
        self.status_label.config(text="🔍 Coloque y quite su dedo del sensor 3 veces...")
        self.countdown_label.config(text="📱", fg='#3498db')
        
        # Deshabilitar botón de cancelar durante la captura
        self.cancel_button.config(state=DISABLED)
        
        # Iniciar captura en un hilo separado
        threading.Thread(target=self.capture_fingerprint, daemon=True).start()
        
    def capture_fingerprint(self):
        success = False
        message = ""
        
        try:
            # Inicializar SDK
            res = ftrdll.FTRInitialize()
            if res != FTR_RETCODE_OK:
                message = f"Error al inicializar SDK: {res}"
                self.show_result(success, message)
                return

            # Configurar parámetros (código igual al original)
            res = ftrdll.FTRSetParam(FTR_PARAM_CB_FRAME_SOURCE, FSD_FUTRONIC_USB)
            if res != FTR_RETCODE_OK:
                message = f"Error al configurar fuente de frame: {res}"
                ftrdll.FTRTerminate()
                self.show_result(success, message)
                return

            res = ftrdll.FTRSetParam(FTR_PARAM_CB_CONTROL, cbControl)
            if res != FTR_RETCODE_OK:
                message = f"Error al configurar control: {res}"
                ftrdll.FTRTerminate()
                self.show_result(success, message)
                return

            # Configurar otros parámetros
            ftrdll.FTRSetParam(FTR_PARAM_MAX_FARN_REQUESTED, c_ulong(245))
            ftrdll.FTRSetParam(FTR_PARAM_FAKE_DETECT, c_ulong(0))
            ftrdll.FTRSetParam(FTR_PARAM_FFD_CONTROL, c_ulong(0))
            ftrdll.FTRSetParam(FTR_PARAM_MIOT_CONTROL, c_ulong(0))
            ftrdll.FTRSetParam(FTR_PARAM_MAX_MODELS, c_ulong(3))
            ftrdll.FTRSetParam(FTR_PARAM_VERSION, FTR_VERSION_CURRENT)

            # Obtener tamaño de plantilla
            enrolSample = FtrData()
            res = ftrdll.FTRGetParam(FTR_PARAM_MAX_TEMPLATE_SIZE, byref(enrolSample, FtrData.dwsize.offset))
            if res != FTR_RETCODE_OK:
                message = f"Error al obtener parámetros: {res}"
                ftrdll.FTRTerminate()
                self.show_result(success, message)
                return

            # Reservar buffer para la plantilla
            buf = create_string_buffer(enrolSample.dwsize)
            enrolSample.pdata = cast(buf, c_void_p)

            eData = FtrEnrollData()
            eData.dwsize = sizeof(FtrEnrollData)

            # Capturar la huella
            res = ftrdll.FTREnrollX(None, FTR_PURPOSE_ENROLL, byref(enrolSample), byref(eData))
            if res != FTR_RETCODE_OK:
                message = f"Error en la captura de huella: {res}"
                ftrdll.FTRTerminate()
                self.show_result(success, message)
                return

            # Convertir plantilla a bytes
            template_bytes = string_at(enrolSample.pdata, enrolSample.dwsize)

            # Usar el ID proporcionado o obtener el último
            estudiante_id = self.estudiante_id if self.estudiante_id else obtener_ultimo_estudiante()
            if not estudiante_id:
                message = "No se pudo obtener el ID del estudiante"
                ftrdll.FTRTerminate()
                self.show_result(success, message)
                return

            # VALIDACIÓN DE DUPLICADOS
            self.root.after(0, lambda: self.status_label.config(text="🔍 Verificando si la huella ya existe..."))
            
            es_duplicada, estudiante_existente_id = verificar_huella_duplicada_mejorado(
                template_bytes, 
                estudiante_id
            )
            
            if es_duplicada:
                # Obtener datos del estudiante existente
                datos_estudiante = obtener_datos_estudiante(estudiante_existente_id)
                if datos_estudiante:
                    message = f"Esta huella ya está registrada para el estudiante: {datos_estudiante['nombre']} {datos_estudiante['apellido']} (Cédula: {datos_estudiante['cedula']})"
                else:
                    message = f"Esta huella ya está registrada para otro estudiante (ID: {estudiante_existente_id})"
                
                ftrdll.FTRTerminate()
                self.show_result(success, message)
                return

            # Si no es duplicada, guardar
            if guardar_huella(estudiante_id, template_bytes, eData.dwquality):
                success = True
                message = f"Huella registrada correctamente para el estudiante {estudiante_id}. Calidad: {eData.dwquality}"
            else:
                message = "Error al guardar la huella en la base de datos"

            ftrdll.FTRTerminate()
            
        except Exception as e:
            try:
                ftrdll.FTRTerminate()
            except:
                pass
            message = f"Error inesperado: {str(e)}"
            
        self.show_result(success, message)
        
    def show_result(self, success, message):
        self.root.after(0, lambda: self.update_ui_result(success, message))
        
    def update_ui_result(self, success, message):
        self.capturing = False
        self.cancel_button.config(state=NORMAL)
        
        if success:
            self.status_label.config(text="¡Huella registrada exitosamente!", fg='#27ae60')
            self.countdown_label.config(text="✅", fg='#27ae60')
        else:
            self.status_label.config(text="Error en el registro", fg='#e74c3c')
            self.countdown_label.config(text="❌", fg='#e74c3c')
            
        # Mostrar resultado por 2.5 segundos y luego cerrar
        self.root.after(2500, lambda: self.close_with_result(success, message))
        
    def close_with_result(self, success, message):
        # Imprimir resultado JSON para compatibilidad con Laravel
        result = {
            "success": success,
            "message": message
        }
        print(json.dumps(result))
        self.root.quit()
        
    def cancel_operation(self):
        self.countdown_active = False
        if not self.capturing:
            result = {
                "success": False,
                "message": "Operación cancelada por el usuario"
            }
            print(json.dumps(result))
            self.root.quit()
            
    def on_closing(self):
        if self.capturing:
            return  # No permitir cerrar durante la captura
        self.cancel_operation()
        
    def run(self):
        self.root.mainloop()

def main():
    try:
        # Verificar si se pasó un ID como argumento
        estudiante_id = None
        if len(sys.argv) > 1:
            try:
                estudiante_id = int(sys.argv[1])
            except ValueError:
                result = {
                    "success": False,
                    "message": "ID de estudiante inválido"
                }
                print(json.dumps(result))
                sys.exit(1)
        
        app = FingerprintEnrollGUI(estudiante_id)
        app.run()
    except Exception as e:
        result = {
            "success": False,
            "message": f"Error inesperado: {str(e)}"
        }
        print(json.dumps(result))
        sys.exit(1)

if __name__ == "__main__":
    main()