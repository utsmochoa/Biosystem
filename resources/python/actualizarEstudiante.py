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
# 4) Conexión a la base de datos
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

def actualizar_huella(estudiante_id, template_bytes, calidad):
    conexion = conectar_bd()
    if not conexion:
        return False
    try:
        cursor = conexion.cursor()
        query = "UPDATE huellas_digitales SET huella_data = %s, quality = %s WHERE estudiante_id = %s"
        cursor.execute(query, (template_bytes, calidad, estudiante_id))
        conexion.commit()
        return True
    except Error as e:
        return False
    finally:
        cursor.close()
        conexion.close()

class FingerprintGUI:
    def __init__(self, estudiante_id):
        self.estudiante_id = estudiante_id
        self.root = Tk()
        self.setup_window()
        self.create_widgets()
        self.countdown_active = False
        self.capturing = False
        
        # Iniciar countdown automáticamente después de crear la ventana
        self.root.after(500, self.start_countdown)
        
    def setup_window(self):
        self.root.title("Actualizar Huella Digital")
        self.root.geometry("400x300")
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
                           text="Actualización de Huella Digital", 
                           font=('Arial', 14, 'bold'),
                           bg='#f0f0f0',
                           fg='#2c3e50')
        title_label.pack(pady=(0, 10))
        
        # ID del estudiante
        id_label = Label(main_frame, 
                        text=f"Estudiante ID: {self.estudiante_id}", 
                        font=('Arial', 10),
                        bg='#f0f0f0',
                        fg='#7f8c8d')
        id_label.pack(pady=(0, 20))
        
        # Ícono de huella (simulado con texto)
        icon_label = Label(main_frame, 
                          text="👆", 
                          font=('Arial', 48),
                          bg='#f0f0f0')
        icon_label.pack(pady=(0, 30))
        
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
        
        # Solo botón Cancelar
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
        self.status_label.config(text="🔍 Coloque y quite su dedo del sensor 5 veces...")
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
            if res != 0:
                message = f"Error al inicializar SDK: {res}"
                self.show_result(success, message)
                return

            ftrdll.FTRSetParam(FTR_PARAM_CB_FRAME_SOURCE, FSD_FUTRONIC_USB)
            ftrdll.FTRSetParam(FTR_PARAM_CB_CONTROL, cbControl)
            ftrdll.FTRSetParam(FTR_PARAM_VERSION, FTR_VERSION_CURRENT)

            enrolSample = FtrData()
            res = ftrdll.FTRGetParam(FTR_PARAM_MAX_TEMPLATE_SIZE, byref(enrolSample, FtrData.dwsize.offset))
            if res != 0:
                message = f"Error al obtener parámetros: {res}"
                ftrdll.FTRTerminate()
                self.show_result(success, message)
                return

            buf = create_string_buffer(enrolSample.dwsize)
            enrolSample.pdata = cast(buf, c_void_p)

            eData = FtrEnrollData()
            eData.dwsize = sizeof(FtrEnrollData)

            res = ftrdll.FTREnrollX(None, FTR_PURPOSE_ENROLL, byref(enrolSample), byref(eData))
            if res != 0:
                message = f"Error en la captura de huella: {res}"
                ftrdll.FTRTerminate()
                self.show_result(success, message)
                return

            template_bytes = string_at(enrolSample.pdata, enrolSample.dwsize)

            if actualizar_huella(self.estudiante_id, template_bytes, eData.dwquality):
                success = True
                message = f"Huella actualizada correctamente. Calidad: {eData.dwquality}"
            else:
                message = "Error al guardar la huella en la base de datos"

            ftrdll.FTRTerminate()
            
        except Exception as e:
            message = f"Error inesperado: {str(e)}"
            
        self.show_result(success, message)
        
    def show_result(self, success, message):
        self.root.after(0, lambda: self.update_ui_result(success, message))
        
    def update_ui_result(self, success, message):
        self.capturing = False
        self.cancel_button.config(state=NORMAL)
        
        if success:
            self.status_label.config(text="¡Huella capturada exitosamente!", fg='#27ae60')
            self.countdown_label.config(text="✅", fg='#27ae60')
        else:
            self.status_label.config(text="Error en la captura", fg='#e74c3c')
            self.countdown_label.config(text="❌", fg='#e74c3c')
            
        # Mostrar resultado por 2.5 segundos y luego cerrar
        self.root.after(2500, lambda: self.close_with_result(success, message))
        
    def close_with_result(self, success, message):
        # Imprimir resultado JSON para Laravel
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
    if len(sys.argv) < 2:
        result = {
            "success": False,
            "message": "ID de estudiante no proporcionado"
        }
        print(json.dumps(result))
        sys.exit(1)

    try:
        estudiante_id = int(sys.argv[1])
        app = FingerprintGUI(estudiante_id)
        app.run()
    except ValueError:
        result = {
            "success": False,
            "message": "ID de estudiante inválido"
        }
        print(json.dumps(result))
        sys.exit(1)
    except Exception as e:
        result = {
            "success": False,
            "message": f"Error inesperado: {str(e)}"
        }
        print(json.dumps(result))
        sys.exit(1)

if __name__ == "__main__":
    main()