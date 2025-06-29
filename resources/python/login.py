import os
import ctypes
from ctypes import *
from tkinter import *
from tkinter import messagebox
import mysql.connector
from mysql.connector import Error
import json
import sys

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
# 4) Conexión a la base de datos
# ------------------------------------------------------------------------------
def conectar_bd():
    try:
        conexion = mysql.connector.connect(
            host='localhost',
            user='BioSystem',
            password='huella2128',
            database='biosystem'
        )
        return conexion
    except Error as e:
        print(f"Error al conectar a la base de datos: {e}")
        messagebox.showerror("Error de Base de Datos", f"No se pudo conectar a la base de datos: {e}")
        return None

def obtener_datos_estudiante(users_id):
    conexion = conectar_bd()
    if conexion:
        try:
            cursor = conexion.cursor(dictionary=True)
            query = "SELECT * FROM users WHERE id = %s"
            cursor.execute(query, (users_id,))
            estudiante = cursor.fetchone()
            cursor.close()
            conexion.close()
            return estudiante
        except Error as e:
            print(f"Error al obtener datos del estudiante: {e}")
            return None

def obtener_todas_huellas():
    conexion = conectar_bd()
    if conexion:
        try:
            cursor = conexion.cursor(dictionary=True)
            query = "SELECT id, users_id, huella_data FROM huellas_users"
            cursor.execute(query)
            huellas = cursor.fetchall()
            cursor.close()
            conexion.close()
            return huellas
        except Error as e:
            print(f"Error al obtener huellas: {e}")
            return None

# ------------------------------------------------------------------------------
# 5) Callback de control
# ------------------------------------------------------------------------------
@CFUNCTYPE(c_void_p, c_void_p, c_ulong, c_void_p, c_ulong, c_void_p)
def cbControl(context, state, response, bitmap, signal):
    cast(response, POINTER(c_ulong)).contents.value = FTR_CB_RESP_CONTINUE.value
    
    msg = ""
    if state & FTR_STATE_SIGNAL_PROVIDED:
        if signal == 1:
            msg = "Coloca tu huella en el escáner"
        elif signal == 2:
            msg = "Quita tu huella del escáner"
        elif signal == 3:
            msg = "Huella falsa detectada"
        else:
            msg = "Señal no definida"
    
    # Se podría agregar un Label para mostrar estos mensajes en la GUI
    print(msg)
    
    return None

# ------------------------------------------------------------------------------
# 6) Función para retornar resultado en formato JSON
# ------------------------------------------------------------------------------
def retornar_resultado(users_id=None, error=None, success=False):
    resultado = {
        "success": success,
        "users_id": users_id,
        "error": error,
        
    }
    
    # Imprimir el resultado en formato JSON para que Laravel lo capture
    print(json.dumps(resultado))
    
    # Terminar el proceso con código 0 (éxito)
    sys.exit(0)

# ------------------------------------------------------------------------------
# 7) Función de Verificación con la base de datos
# ------------------------------------------------------------------------------
def verify_fingerprint_with_database(root, estatus_label):
    # Inicializar la API
    res = ftrdll.FTRInitialize()
    if res != 0:
        mensaje_error = f"FTRInitialize falló con código {res}"
        estatus_label.config(text=mensaje_error)
        root.update()
        retornar_resultado(error=mensaje_error)
        root.after(2000, root.destroy)
        return

    # Configurar parámetros básicos
    res = ftrdll.FTRSetParam(FTR_PARAM_CB_FRAME_SOURCE, FSD_FUTRONIC_USB)
    if res != 0:
        mensaje_error = f"No se detecta dispositivo biometrico. Cod: {res}"
        estatus_label.config(text=mensaje_error)
        root.update()
        retornar_resultado(error=mensaje_error)
        ftrdll.FTRTerminate()
        root.after(2000, root.destroy)
        return

    res = ftrdll.FTRSetParam(FTR_PARAM_CB_CONTROL, cbControl)
    if res != 0:
        mensaje_error = f"FTRSetParam(CB_CONTROL) falló con código {res}"
        estatus_label.config(text=mensaje_error)
        root.update()
        retornar_resultado(error=mensaje_error)
        ftrdll.FTRTerminate()
        root.after(2000, root.destroy)
        return

    # Configurar el nivel de seguridad (FARN) - Ajustado a un valor menos estricto
    ftrdll.FTRSetParam(FTR_PARAM_MAX_FARN_REQUESTED, c_ulong(500))  # Valor ajustado para ser menos exigente
    
    # Configurar otros parámetros
    ftrdll.FTRSetParam(FTR_PARAM_FAKE_DETECT, c_ulong(0))
    ftrdll.FTRSetParam(FTR_PARAM_FFD_CONTROL, c_ulong(0))
    ftrdll.FTRSetParam(FTR_PARAM_MIOT_CONTROL, c_ulong(0))
    ftrdll.FTRSetParam(FTR_PARAM_MAX_MODELS, c_ulong(3))
    ftrdll.FTRSetParam(FTR_PARAM_VERSION, FTR_VERSION_CURRENT)

    # Obtener tamaño máximo de la plantilla
    max_template_size = c_ulong(0)
    res = ftrdll.FTRGetParam(FTR_PARAM_MAX_TEMPLATE_SIZE, byref(max_template_size))
    if res != 0:
        mensaje_error = f"FTRGetParam falló con código {res}"
        estatus_label.config(text=mensaje_error)
        root.update()
        retornar_resultado(error=mensaje_error)
        ftrdll.FTRTerminate()
        root.after(2000, root.destroy)
        return

    # Actualizar etiqueta de estado
    estatus_label.config(text="Coloque su dedo en el lector...")
    root.update()

    # Obtener todas las huellas de la base de datos
    huellas = obtener_todas_huellas()
    if not huellas:
        mensaje_error = "No se pudieron obtener las huellas de la base de datos"
        estatus_label.config(text=mensaje_error)
        root.update()
        retornar_resultado(error=mensaje_error)
        ftrdll.FTRTerminate()
        root.after(2000, root.destroy)
        return

    # Variables para el resultado de verificación
    match_found = False
    users_id_match = None
    mejor_farn = 100000  # Valor alto inicial
    
    # Comparar con cada huella en la base de datos
    for huella in huellas:
        try:
            # Preparar estructuras para la verificación
            db_sample = FtrData()
            db_sample.dwsize = len(huella['huella_data'])
            
            # Asegurarse de que los datos de la huella son válidos
            if db_sample.dwsize == 0:
                print(f"Advertencia: Huella ID {huella['id']} tiene tamaño 0, saltando")
                continue
                
            db_buf = create_string_buffer(huella['huella_data'], len(huella['huella_data']))
            db_sample.pdata = cast(db_buf, c_void_p)
            
            # Variables para esta verificación
            result = c_long(0)
            ftr_farn = c_ulong(0)
            
            # Verificar la huella capturada contra la huella de la BD
            res = ftrdll.FTRVerifyN(None, byref(db_sample), byref(result), byref(ftr_farn))
            
            
            
            if res == 0:  # La función se ejecutó correctamente
                if result.value == 1:  # Coincidencia encontrada
                    if ftr_farn.value < mejor_farn:
                        mejor_farn = ftr_farn.value
                        users_id_match = huella['users_id']
                        match_found = True
                        print(f"¡Coincidencia encontrada! Usuario ID: {users_id_match}, FARN: {ftr_farn.value}")
                        break
        except Exception as e:
            print(f"Error al procesar huella ID {huella['id']}: {e}")
    
    # Finalizar la API
    ftrdll.FTRTerminate()
    
    # Mostrar resultados
    if match_found and users_id_match:
        # Mostrar mensaje de éxito
        estatus_label.config(text="¡Huella verificada! Redirigiendo...")
        root.update()
        
        # Retornar resultado exitoso con el ID del estudiante
        retornar_resultado(users_id=users_id_match, success=True)
    else:
        estatus_label.config(text="No se encontró coincidencia")
        root.update()
        
        retornar_resultado(error="No se encontró coincidencia en la base de datos")
        root.destroy()
    
    root.after(2000, root.destroy)

# ------------------------------------------------------------------------------
# 8) Interfaz gráfica con ejecución automática
# ------------------------------------------------------------------------------
def main():
    root = Tk()
    root.title("Verificación de Huella Digital")
    root.geometry("400x150")  # Ventana más grande para mejor visualización
    
    # Establecer la ventana como topmost (siempre visible)
    root.attributes('-topmost', True)
    
    # Crear una etiqueta de instrucción
    lbl_instruccion = Label(root, text="Iniciando sistema de verificación...", font=("Arial", 12))
    lbl_instruccion.pack(pady=20)
    
    # Etiqueta para mostrar estado
    lbl_estatus = Label(root, text="", font=("Arial", 10), fg="blue")
    lbl_estatus.pack(pady=10)
    
    # Centrar ventana en la pantalla
    root.update_idletasks()
    width = root.winfo_width()
    height = root.winfo_height()
    x = (root.winfo_screenwidth() // 2) - (width // 2)
    y = (root.winfo_screenheight() // 2) - (height // 2)
    root.geometry('{}x{}+{}+{}'.format(width, height, x, y))
    
    # Asegurarse de que la ventana está en primer plano
    root.lift()
    root.focus_force()
    
    # Ejecutar la función después de 1000ms para dar tiempo a que la GUI se inicialice
    root.after(1000, lambda: verify_fingerprint_with_database(root, lbl_estatus))
    
    root.mainloop()

if __name__ == "__main__":
    main()