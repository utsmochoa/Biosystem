import os
import ctypes
from ctypes import *
from tkinter import *
from tkinter import messagebox
import mysql.connector
from mysql.connector import Error
import json
import sys
import threading
import time

# ------------------------------------------------------------------------------
# 1) Cargar la DLL del SDK
# ------------------------------------------------------------------------------
ftrdll = CDLL('ftrapi.dll')

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
# 3) Definición de estructuras
# ------------------------------------------------------------------------------
class FtrData(Structure):
    _pack_ = 1
    _fields_ = [
        ('dwsize', c_ulong),
        ('pdata', c_void_p)
    ]

# ------------------------------------------------------------------------------
# 4) Database Manager
# ------------------------------------------------------------------------------
class DatabaseManager:
    def __init__(self):
        self.config = {
            'host': 'localhost',
            'user': 'BioSystem',
            'password': 'huella2128',
            'database': 'biosystem',
            'autocommit': True
        }
        
    def get_connection(self):
        try:
            return mysql.connector.connect(**self.config)
        except Error as e:
            print(f"Error al conectar a la base de datos: {e}")
            return None

db_manager = DatabaseManager()

def obtener_huellas_optimizadas():
    """Obtiene todas las huellas de la base de datos"""
    conexion = db_manager.get_connection()
    if not conexion:
        return None
        
    try:
        cursor = conexion.cursor(dictionary=True)
        query = """
        SELECT h.id, h.estudiante_id, h.huella_data, h.quality
        FROM huellas_digitales h 
        INNER JOIN estudiantes e ON h.estudiante_id = e.id 
        WHERE h.activo = 1 
        ORDER BY h.quality DESC
        """
        cursor.execute(query)
        huellas = cursor.fetchall()
        cursor.close()
        conexion.close()
        
        print(f"Se obtuvieron {len(huellas)} huellas de la base de datos")
        return huellas
        
    except Error as e:
        print(f"Error al obtener huellas: {e}")
        if conexion:
            conexion.close()
        return None

# ------------------------------------------------------------------------------
# 5) Callback de control
# ------------------------------------------------------------------------------
@CFUNCTYPE(c_void_p, c_void_p, c_ulong, c_void_p, c_ulong, c_void_p)
def cbControl(context, state, response, bitmap, signal):
    cast(response, POINTER(c_ulong)).contents.value = FTR_CB_RESP_CONTINUE.value
    
    if state & FTR_STATE_SIGNAL_PROVIDED:
        if signal == 1:
            print("Coloca tu huella en el escáner")
        elif signal == 2:
            print("Quita tu huella del escáner")
        elif signal == 3:
            print("Huella falsa detectada")
    
    return None

# ------------------------------------------------------------------------------
# 6) Función para retornar resultado
# ------------------------------------------------------------------------------
def retornar_resultado(estudiante_id=None, error=None, success=False):
    resultado = {
        "success": success,
        "estudiante_id": estudiante_id,
        "error": error,
        "redirect_url": f"/biosystem/ingreso/informacion/{estudiante_id}" if estudiante_id else None
    }
    
    print(json.dumps(resultado))
    sys.exit(0)

# ------------------------------------------------------------------------------
# 7) Función principal de verificación - CORREGIDA
# ------------------------------------------------------------------------------
def verify_fingerprint_with_database_optimized(root, estatus_label):
    """
    Función principal de verificación usando FTRVerifyN correctamente
    """
    # 1. INICIALIZAR SDK
    print("Inicializando SDK...")
    res = ftrdll.FTRInitialize()
    if res != 0:
        mensaje_error = f"FTRInitialize falló con código {res}"
        estatus_label.config(text=mensaje_error)
        root.update()
        retornar_resultado(error=mensaje_error)
        return

    # 2. CONFIGURAR PARÁMETROS
    print("Configurando parámetros...")
    res = ftrdll.FTRSetParam(FTR_PARAM_CB_FRAME_SOURCE, FSD_FUTRONIC_USB)
    if res != 0:
        mensaje_error = f"FTRSetParam(CB_FRAME_SOURCE) falló con código {res}"
        estatus_label.config(text=mensaje_error)
        root.update()
        ftrdll.FTRTerminate()
        retornar_resultado(error=mensaje_error)
        return

    res = ftrdll.FTRSetParam(FTR_PARAM_CB_CONTROL, cbControl)
    if res != 0:
        mensaje_error = f"FTRSetParam(CB_CONTROL) falló con código {res}"
        estatus_label.config(text=mensaje_error)
        root.update()
        ftrdll.FTRTerminate()
        retornar_resultado(error=mensaje_error)
        return

    # Configurar otros parámetros
    ftrdll.FTRSetParam(FTR_PARAM_MAX_FARN_REQUESTED, c_ulong(500))
    ftrdll.FTRSetParam(FTR_PARAM_FAKE_DETECT, c_ulong(0))
    ftrdll.FTRSetParam(FTR_PARAM_FFD_CONTROL, c_ulong(0))
    ftrdll.FTRSetParam(FTR_PARAM_MIOT_CONTROL, c_ulong(0))
    ftrdll.FTRSetParam(FTR_PARAM_MAX_MODELS, c_ulong(3))
    ftrdll.FTRSetParam(FTR_PARAM_VERSION, FTR_VERSION_CURRENT)

    # 3. OBTENER HUELLAS DE LA BASE DE DATOS
    print("Obteniendo huellas de la base de datos...")
    estatus_label.config(text="Cargando huellas de la base de datos...")
    root.update()
    
    huellas = obtener_huellas_optimizadas()
    if not huellas:
        mensaje_error = "No se pudieron obtener las huellas de la base de datos"
        estatus_label.config(text=mensaje_error)
        root.update()
        ftrdll.FTRTerminate()
        retornar_resultado(error=mensaje_error)
        return

    print(f"Verificando contra {len(huellas)} huellas registradas...")

    # 4. VERIFICACIÓN CON FTRVerifyN
    estatus_label.config(text="Coloque su dedo en el lector...")
    root.update()

    mejor_match = None
    mejor_farn = 10000

    for i, huella in enumerate(huellas):
        # Actualizar progreso
        if i % 20 == 0:
            progreso = f"Verificando huella {i+1}/{len(huellas)}"
            estatus_label.config(text=progreso)
            root.update()

        try:
            # Preparar la huella de la BD
            huella_data = huella['huella_data']
            if not huella_data or len(huella_data) == 0:
                continue

            db_sample = FtrData()
            db_sample.dwsize = len(huella_data)
            db_buf = create_string_buffer(huella_data, len(huella_data))
            db_sample.pdata = cast(db_buf, c_void_p)

            # Variables para el resultado
            result = c_long(0)
            ftr_farn = c_ulong(0)

            # LLAMAR A FTRVerifyN - esto maneja la captura automáticamente
            res = ftrdll.FTRVerifyN(None, byref(db_sample), byref(result), byref(ftr_farn))

            if res == 0:  # Success
                if result.value == 1:  # Match found
                    print(f"¡COINCIDENCIA! Estudiante ID: {huella['estudiante_id']}, FARN: {ftr_farn.value}")
                    
                    if ftr_farn.value < mejor_farn:
                        mejor_farn = ftr_farn.value
                        mejor_match = huella['estudiante_id']
                        
                        # Si es una coincidencia muy buena, parar aquí
                        if ftr_farn.value < 100:
                            print("Coincidencia de alta confianza encontrada!")
                            break
            else:
                print(f"FTRVerifyN falló con código: {res} para huella ID: {huella['id']}")

        except Exception as e:
            print(f"Error procesando huella ID {huella['id']}: {e}")
            continue

    # 5. FINALIZAR SDK
    ftrdll.FTRTerminate()

    # 6. MOSTRAR RESULTADOS
    if mejor_match:
        estatus_label.config(text="¡Huella verificada! Redirigiendo...")
        root.update()
        print(f"Mejor coincidencia: Estudiante ID {mejor_match} con FARN {mejor_farn}")
        retornar_resultado(estudiante_id=mejor_match, success=True)
    else:
        estatus_label.config(text="No se encontró coincidencia")
        root.update()
        retornar_resultado(error="No se encontró coincidencia en la base de datos")

    root.after(2000, root.destroy)

# ------------------------------------------------------------------------------
# 8) Interfaz gráfica
# ------------------------------------------------------------------------------
def main():
    root = Tk()
    root.title("Verificación de Huella Digital")
    root.geometry("450x180")
    root.attributes('-topmost', True)
    
    main_frame = Frame(root, padx=20, pady=20)
    main_frame.pack(expand=True, fill='both')
    
    lbl_instruccion = Label(main_frame, text="Sistema de Verificación Biométrica", 
                           font=("Arial", 14, "bold"))
    lbl_instruccion.pack(pady=(0, 10))
    
    lbl_estatus = Label(main_frame, text="Iniciando sistema...", 
                       font=("Arial", 11), fg="blue", wraplength=400)
    lbl_estatus.pack(pady=10)
    
    # Centrar ventana
    root.update_idletasks()
    width = root.winfo_width()
    height = root.winfo_height()
    x = (root.winfo_screenwidth() // 2) - (width // 2)
    y = (root.winfo_screenheight() // 2) - (height // 2)
    root.geometry('{}x{}+{}+{}'.format(width, height, x, y))
    
    root.lift()
    root.focus_force()
    
    # Ejecutar verificación
    root.after(1000, lambda: verify_fingerprint_with_database_optimized(root, lbl_estatus))
    
    root.mainloop()

if __name__ == "__main__":
    main()