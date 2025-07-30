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
        print(f"Error de conexión a BD: {e}")
        return None

def verificar_estudiante_existente(estudiante_id):
    """
    Verifica si el estudiante existe y está activo
    """
    conexion = conectar_bd()
    if not conexion:
        return False, "Error de conexión a la base de datos"
    
    try:
        cursor = conexion.cursor()
        query = "SELECT id, nombres, apellidos, cedula_identidad FROM estudiantes WHERE id = %s AND activo = 1"
        cursor.execute(query, (estudiante_id,))
        estudiante = cursor.fetchone()
        cursor.close()
        conexion.close()
        
        if estudiante:
            return True, {
                'id': estudiante[0],
                'nombres': estudiante[1],
                'apellidos': estudiante[2],
                'cedula': estudiante[3]
            }
        else:
            return False, "Estudiante no encontrado o inactivo"
            
    except Error as e:
        print(f"Error al verificar estudiante: {e}")
        return False, f"Error de base de datos: {e}"

def verificar_estudiante_sin_huella(estudiante_id):
    """
    Verifica que el estudiante NO tenga huella registrada
    """
    conexion = conectar_bd()
    if not conexion:
        return False, "Error de conexión a la base de datos"
    
    try:
        cursor = conexion.cursor()
        query = "SELECT COUNT(*) FROM huellas_digitales WHERE estudiante_id = %s"
        cursor.execute(query, (estudiante_id,))
        count = cursor.fetchone()[0]
        cursor.close()
        conexion.close()
        
        if count > 0:
            return False, "Este estudiante ya tiene una huella registrada"
        else:
            return True, "Estudiante sin huella registrada"
            
    except Error as e:
        print(f"Error al verificar huella: {e}")
        return False, f"Error de base de datos: {e}"

def verificar_huella_duplicada_global(template_bytes):
    """
    Verifica si la huella capturada ya existe en toda la base de datos
    """
    conexion = conectar_bd()
    if not conexion:
        print("Error: No se pudo conectar a la base de datos para verificar duplicados")
        return False, None
    
    try:
        cursor = conexion.cursor()
        # Obtener todas las huellas existentes
        query = "SELECT estudiante_id, huella_data FROM huellas_digitales WHERE activo = 1"
        cursor.execute(query)
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

def obtener_datos_estudiante_por_id(estudiante_id):
    """
    Obtiene los datos completos de un estudiante por su ID
    """
    conexion = conectar_bd()
    if not conexion:
        return None
    
    try:
        cursor = conexion.cursor()
        query = "SELECT nombres, apellidos, cedula_identidad FROM estudiantes WHERE id = %s"
        cursor.execute(query, (estudiante_id,))
        estudiante = cursor.fetchone()
        cursor.close()
        conexion.close()
        
        if estudiante:
            return {
                'nombres': estudiante[0],
                'apellidos': estudiante[1],
                'cedula': estudiante[2]
            }
        return None
        
    except Error as e:
        print(f"Error al obtener datos del estudiante: {e}")
        return None

def guardar_huella(estudiante_id, template_bytes, calidad):
    """
    Guarda la huella digital en la base de datos
    """
    conexion = conectar_bd()
    if not conexion:
        return False, "Error de conexión a la base de datos"
    
    try:
        cursor = conexion.cursor()
        query = "INSERT INTO huellas_digitales (estudiante_id, huella_data, quality, activo, fecha_registro) VALUES (%s, %s, %s, %s, NOW())"
        cursor.execute(query, (estudiante_id, template_bytes, calidad, 1))
        conexion.commit()
        cursor.close()
        conexion.close()
        return True, "Huella guardada correctamente"
    except Error as e:
        print(f"Error al guardar huella: {e}")
        return False, f"Error al guardar en la base de datos: {e}"

class AgregarHuellaExistenteGUI:
    def __init__(self, estudiante_id):
        self.estudiante_id = estudiante_id
        self.estudiante_data = None
        
        # Validaciones iniciales
        if not self.estudiante_id:
            self.exit_with_error("No se proporcionó el ID del estudiante")
            return
        
        # Verificar que el estudiante existe
        existe, data = verificar_estudiante_existente(self.estudiante_id)
        if not existe:
            self.exit_with_error(data)
            return
        
        self.estudiante_data = data
        
        # Verificar que el estudiante NO tiene huella
        sin_huella, mensaje = verificar_estudiante_sin_huella(self.estudiante_id)
        if not sin_huella:
            self.exit_with_error(mensaje)
            return
        
        self.root = Tk()
        self.setup_window()
        self.create_widgets()
        self.countdown_active = False
        self.capturing = False
        
        # Iniciar countdown automáticamente después de crear la ventana
        self.root.after(500, self.start_countdown)
    
    def exit_with_error(self, message):
        result = {
            "success": False,
            "message": message
        }
        print(json.dumps(result))
        sys.exit(1)
        
    def setup_window(self):
        self.root.title("Añadir Huella a Estudiante Existente")
        self.root.geometry("450x400")
        self.root.resizable(False, False)
        
        # Hacer que la ventana esté siempre al frente
        self.root.attributes('-topmost', True)
        
        # Centrar la ventana
        self.root.eval('tk::PlaceWindow . center')
        
        # Configurar el cierre de la ventana
        self.root.protocol("WM_DELETE_WINDOW", self.on_closing)
        
        # Configurar estilo
        self.root.configure(bg='#f8f9fa')
        
    def create_widgets(self):
        # Frame principal
        main_frame = Frame(self.root, bg='#f8f9fa', padx=25, pady=25)
        main_frame.pack(fill=BOTH, expand=True)
        
        # Título
        title_label = Label(main_frame, 
                           text="Añadir Huella Digital", 
                           font=('Arial', 16, 'bold'),
                           bg='#f8f9fa',
                           fg='#2c3e50')
        title_label.pack(pady=(0, 5))
        
        subtitle_label = Label(main_frame, 
                              text="Estudiante Existente", 
                              font=('Arial', 12),
                              bg='#f8f9fa',
                              fg='#7f8c8d')
        subtitle_label.pack(pady=(0, 20))
        
        # Información del estudiante en un frame con borde
        info_frame = Frame(main_frame, bg='#ffffff', relief=RIDGE, bd=1)
        info_frame.pack(fill=X, pady=(0, 20), padx=10)
        
        info_title = Label(info_frame, 
                          text="📋 Información del Estudiante", 
                          font=('Arial', 11, 'bold'),
                          bg='#ffffff',
                          fg='#34495e')
        info_title.pack(pady=(10, 5))
        
        info_text = f"👤 {self.estudiante_data['nombres']} {self.estudiante_data['apellidos']}\n🆔 Cédula: {self.estudiante_data['cedula']}\n🔢 ID: {self.estudiante_data['id']}"
        info_label = Label(info_frame, 
                          text=info_text, 
                          font=('Arial', 10),
                          bg='#ffffff',
                          fg='#2c3e50',
                          justify=LEFT)
        info_label.pack(pady=(0, 10))
        
        # Separador
        separator = Frame(main_frame, height=2, bg='#ecf0f1')
        separator.pack(fill=X, pady=(0, 20))
        
        # Ícono de huella
        icon_label = Label(main_frame, 
                          text="👆", 
                          font=('Arial', 48),
                          bg='#f8f9fa')
        icon_label.pack(pady=(0, 15))
        
        # Etiqueta de estado
        self.status_label = Label(main_frame, 
                                 text="Preparándose para capturar huella...", 
                                 font=('Arial', 11),
                                 bg='#f8f9fa',
                                 fg='#34495e')
        self.status_label.pack(pady=(0, 10))
        
        # Frame para countdown
        countdown_frame = Frame(main_frame, bg='#f8f9fa')
        countdown_frame.pack(pady=(0, 20))
        
        # Etiqueta de countdown
        self.countdown_label = Label(countdown_frame, 
                                   text="", 
                                   font=('Arial', 36, 'bold'),
                                   bg='#f8f9fa',
                                   fg='#e74c3c')
        self.countdown_label.pack()
        
        # Botones
        button_frame = Frame(main_frame, bg='#f8f9fa')
        button_frame.pack()
        
        self.cancel_button = Button(button_frame, 
                                   text="❌ Cancelar", 
                                   command=self.cancel_operation,
                                   bg='#e74c3c',
                                   fg='white',
                                   font=('Arial', 10, 'bold'),
                                   padx=25,
                                   pady=10,
                                   relief=FLAT,
                                   cursor='hand2')
        self.cancel_button.pack()
        
    def start_countdown(self):
        if self.countdown_active or self.capturing:
            return
            
        self.countdown_active = True
        self.status_label.config(text="🔄 Preparándose para iniciar captura...")
        
        # Iniciar countdown en un hilo separado
        threading.Thread(target=self.countdown_thread, daemon=True).start()
        
    def countdown_thread(self):
        # Countdown de 3 segundos
        for i in range(3, 0, -1):
            if not self.countdown_active:
                return
            self.root.after(0, lambda count=i: self.countdown_label.config(text=str(count)))
            time.sleep(1)
            
        if self.countdown_active:
            self.root.after(0, lambda: self.countdown_label.config(text="¡CAPTURANDO!", fg='#3498db'))
            self.root.after(0, self.start_capture)
            
    def start_capture(self):
        self.countdown_active = False
        self.capturing = True
        self.status_label.config(text="🔍 Coloque y quite su dedo del sensor 3 veces...", fg='#3498db')
        self.countdown_label.config(text="📱", fg='#3498db')
        
        # Deshabilitar botón de cancelar durante la captura
        self.cancel_button.config(state=DISABLED, text="⏳ Capturando...", bg='#95a5a6')
        
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

            # Configurar parámetros
            res = ftrdll.FTRSetParam(FTR_PARAM_CB_FRAME_SOURCE, FSD_FUTRONIC_USB)
            if res != FTR_RETCODE_OK:
                message = f"El dispositivo biometrico no se detecta o no se encuentra conectado. Error: {res}"
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

            # Actualizar estado en la GUI
            self.root.after(0, lambda: self.status_label.config(text="🔍 Capturando huella digital..."))

            # Capturar la huella
            res = ftrdll.FTREnrollX(None, FTR_PURPOSE_ENROLL, byref(enrolSample), byref(eData))
            if res != FTR_RETCODE_OK:
                message = f"Error en la captura de huella: {res}"
                ftrdll.FTRTerminate()
                self.show_result(success, message)
                return

            # Convertir plantilla a bytes
            template_bytes = string_at(enrolSample.pdata, enrolSample.dwsize)

            # VALIDACIÓN DE DUPLICADOS GLOBAL
            self.root.after(0, lambda: self.status_label.config(text="🔍 Verificando si la huella ya existe en el sistema..."))
            
            es_duplicada, estudiante_existente_id = verificar_huella_duplicada_global(template_bytes)
            
            if es_duplicada:
                # Obtener datos del estudiante existente
                datos_estudiante = obtener_datos_estudiante_por_id(estudiante_existente_id)
                if datos_estudiante:
                    message = f"⚠️ Esta huella ya está registrada para: {datos_estudiante['nombres']} {datos_estudiante['apellidos']} (Cédula: {datos_estudiante['cedula']})"
                else:
                    message = f"⚠️ Esta huella ya está registrada para otro estudiante (ID: {estudiante_existente_id})"
                
                ftrdll.FTRTerminate()
                self.show_result(success, message)
                return

            # Si no es duplicada, guardar
            self.root.after(0, lambda: self.status_label.config(text="💾 Guardando huella digital..."))
            
            guardado_exitoso, mensaje_guardado = guardar_huella(self.estudiante_id, template_bytes, eData.dwquality)
            
            if guardado_exitoso:
                success = True
                message = f"✅ Huella registrada correctamente para {self.estudiante_data['nombres']} {self.estudiante_data['apellidos']}. Calidad: {eData.dwquality}"
            else:
                message = f"❌ {mensaje_guardado}"

            ftrdll.FTRTerminate()
            
        except Exception as e:
            try:
                ftrdll.FTRTerminate()
            except:
                pass
            message = f"❌ Error inesperado: {str(e)}"
            
        self.show_result(success, message)
        
    def show_result(self, success, message):
        self.root.after(0, lambda: self.update_ui_result(success, message))
        
    def update_ui_result(self, success, message):
        self.capturing = False
        self.cancel_button.config(state=NORMAL, bg='#e74c3c', text="❌ Cerrar")
        
        if success:
            self.status_label.config(text="🎉 ¡Huella registrada exitosamente!", fg='#27ae60')
            self.countdown_label.config(text="✅", fg='#27ae60')
        else:
            self.status_label.config(text="❌ Error en el registro", fg='#e74c3c')
            self.countdown_label.config(text="❌", fg='#e74c3c')
            
        # Mostrar resultado por 3 segundos y luego cerrar
        self.root.after(3000, lambda: self.close_with_result(success, message))
        
    def close_with_result(self, success, message):
        # Imprimir resultado JSON para compatibilidad con Laravel
        result = {
            "success": success,
            "message": message,
            "estudiante_id": self.estudiante_id,
            "estudiante_nombre": f"{self.estudiante_data['nombres']} {self.estudiante_data['apellidos']}" if self.estudiante_data else None
        }
        print(json.dumps(result))
        self.root.quit()
        
    def cancel_operation(self):
        self.countdown_active = False
        if not self.capturing:
            result = {
                "success": False,
                "message": "⚠️ Operación cancelada por el usuario",
                "estudiante_id": self.estudiante_id
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
        # Verificar argumentos
        if len(sys.argv) != 2:
            result = {
                "success": False,
                "message": "❌ Uso: python agregarHuellaExistente.py <estudiante_id>"
            }
            print(json.dumps(result))
            sys.exit(1)
        
        # Obtener y validar ID del estudiante
        try:
            estudiante_id = int(sys.argv[1])
        except ValueError:
            result = {
                "success": False,
                "message": "❌ El ID del estudiante debe ser un número válido"
            }
            print(json.dumps(result))
            sys.exit(1)
        
        # Crear y ejecutar la aplicación
        app = AgregarHuellaExistenteGUI(estudiante_id)
        app.run()
        
    except Exception as e:
        result = {
            "success": False,
            "message": f"❌ Error inesperado: {str(e)}"
        }
        print(json.dumps(result))
        sys.exit(1)

if __name__ == "__main__":
    main()