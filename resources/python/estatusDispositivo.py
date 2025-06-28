import ctypes
import platform
import json
import os
import sys
import traceback

def load_dll():
    try:
        # Ruta esperada del DLL en misma carpeta
        dll_path = os.path.join(os.path.dirname(__file__), "ftrScanAPI.dll")
        if not os.path.exists(dll_path):
            raise FileNotFoundError(f"No se encontró la DLL en: {dll_path}")
        
        dll = ctypes.WinDLL(dll_path)
        return dll
    except Exception as e:
        raise RuntimeError(f"Error al cargar DLL: {e}")

def main():
    try:
        # 1. Verificar sistema operativo
        if platform.system() != "Windows":
            raise EnvironmentError("Este script solo funciona en Windows.")

        # 2. Cargar DLL
        dll = load_dll()

        # 3. Configurar funciones necesarias
        dll.ftrScanOpenDevice.restype = ctypes.c_void_p
        dll.ftrScanCloseDevice.argtypes = [ctypes.c_void_p]

        # 4. Abrir dispositivo
        handle = dll.ftrScanOpenDevice()
        if not handle:
            raise ConnectionError("Dispositivo Futronic no detectado.")

        # 5. Cerrar conexión
        dll.ftrScanCloseDevice(handle)

        # 6. Imprimir salida en formato JSON
        print(json.dumps({
            "success": True,
            "devices": [
                {
                    "id": "1",
                    "name": "Futronic FS88",
                    "status": "connected"
                }
            ]
        }))

    except Exception as e:
        print(json.dumps({
            "success": False,
            "error": str(e),
            "trace": traceback.format_exc()
        }))
        sys.exit(1)

if __name__ == "__main__":
    main()
