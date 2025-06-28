#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import json
import sys
import platform
from datetime import datetime
import ctypes
from ctypes import wintypes, c_uint32, c_uint16, c_uint8, Structure, POINTER, byref
import subprocess

sys.stdout.reconfigure(encoding='utf-8')

class FTRSCAN_DEVICE_INFO(Structure):
    _fields_ = [
        ("dwStructSize", c_uint32),
        ("byDeviceCompatibility", c_uint8),
        ("wPixelSizeX", c_uint16),
        ("wPixelSizeY", c_uint16)
    ]

class VERSION_STRUCT(Structure):
    _fields_ = [
        ("Major", c_uint32),
        ("Minor", c_uint32),
        ("Build", c_uint32)
    ]

class FTRSCAN_VERSION_INFO(Structure):
    _fields_ = [
        ("dwVersionInfoSize", c_uint32),
        ("APIVersion", VERSION_STRUCT),
        ("HardwareVersion", VERSION_STRUCT),
        ("FirmwareVersion", VERSION_STRUCT)
    ]

class FutronicDevice:
    def __init__(self):
        self.dll = None
        self.device_handle = None
        self.is_initialized = False
        self._load_dll()
    
    def _load_dll(self):
        try:
            if platform.system() != "Windows":
                return
            dll_names = [
                "ftrScanAPI.dll",
                r"C:\Program Files\Futronic\ftrScanAPI.dll",
                r"C:\Program Files (x86)\Futronic\ftrScanAPI.dll"
            ]
            for dll_name in dll_names:
                try:
                    self.dll = ctypes.WinDLL(dll_name)
                    self.is_initialized = True
                    break
                except OSError:
                    continue
            if not self.dll:
                return
            self._setup_function_signatures()
        except:
            self.is_initialized = False

    def _setup_function_signatures(self):
        self.dll.ftrScanOpenDevice.restype = wintypes.HANDLE
        self.dll.ftrScanCloseDevice.restype = wintypes.BOOL
        self.dll.ftrScanCloseDevice.argtypes = [wintypes.HANDLE]
        self.dll.ftrScanGetDeviceInfo.restype = wintypes.BOOL
        self.dll.ftrScanGetDeviceInfo.argtypes = [wintypes.HANDLE, POINTER(FTRSCAN_DEVICE_INFO)]
        self.dll.ftrScanGetVersion.restype = wintypes.BOOL
        self.dll.ftrScanGetVersion.argtypes = [POINTER(FTRSCAN_VERSION_INFO)]
        self.dll.ftrScanGetSerialNumber.restype = wintypes.BOOL
        self.dll.ftrScanGetSerialNumber.argtypes = [wintypes.HANDLE, wintypes.LPSTR, c_uint32]
        self.dll.ftrScanIsFingerPresent.restype = wintypes.BOOL
        self.dll.ftrScanIsFingerPresent.argtypes = [wintypes.HANDLE, POINTER(wintypes.BOOL)]

    def open_device(self):
        self.device_handle = self.dll.ftrScanOpenDevice()
        return self.device_handle != 0 and self.device_handle is not None

    def close_device(self):
        if self.device_handle:
            self.dll.ftrScanCloseDevice(self.device_handle)
            self.device_handle = None

    def get_device_info(self):
        info = FTRSCAN_DEVICE_INFO()
        info.dwStructSize = ctypes.sizeof(FTRSCAN_DEVICE_INFO)
        if self.dll.ftrScanGetDeviceInfo(self.device_handle, byref(info)):
            return info
        return None

    def get_version_info(self):
        version = FTRSCAN_VERSION_INFO()
        version.dwVersionInfoSize = ctypes.sizeof(FTRSCAN_VERSION_INFO)
        if self.dll.ftrScanGetVersion(byref(version)):
            return version
        return None

    def get_serial_number(self):
        buffer = ctypes.create_string_buffer(32)
        if self.dll.ftrScanGetSerialNumber(self.device_handle, buffer, 32):
            return buffer.value.decode('ascii').strip()
        return None

    def is_finger_present(self):
        val = wintypes.BOOL()
        if self.dll.ftrScanIsFingerPresent(self.device_handle, byref(val)):
            return bool(val.value)
        return False

def get_device_model_name(px, py):
    resolution = f"{px}x{py}"
    return {
        "12800x0": "FS88", "320x480": "FS80H", "355x391": "FS88H",
        "400x500": "FS26", "256x360": "FS60", "288x384": "FS50"
    }.get(resolution, f"Modelo desconocido ({resolution})")

def get_device_status():
    base_response = {
        "success": False,
        "details": {
            "device_info": {
                "id": "FTR-001",
                "name": "Scanner Biométrico Futronic",
                "model": "Desconocido",
                "serial_number": None,
                "resolution": None
            },
            "version_info": {
                "FTRAPI_version": "4.0",
                "ftrScanAPI_version": "5.0"
            },
            "sensor_info": {
                "finger_present": False,
                "sensor_active": False
            },
            "connection_info": {
                "port": "USB",
                "driver_status": "OK",
                "system_info": {
                    "os": platform.system(),
                    "architecture": platform.machine()
                }
            }
        }
    }

    if platform.system() != "Windows":
        base_response.update({
            "status": "error",
            "status_code": "UNSUPPORTED_OS",
            "message": "Sistema operativo no compatible",
            "error": f"No compatible con {platform.system()}"
        })
        return json.dumps(base_response, indent=2, ensure_ascii=False)

    device = FutronicDevice()
    if not device.is_initialized:
        base_response.update({
            "status": "error",
            "status_code": "DRIVER_NOT_FOUND",
            "message": "DLL de Futronic no encontrada",
            "error": "Instale el driver del dispositivo"
        })
        return json.dumps(base_response, indent=2, ensure_ascii=False)

    try:
        version_info = device.get_version_info()
        if version_info:
            base_response["details"]["version_info"].update({
                "FTRAPI_version": "4.0",
                "ftrScanAPI_version": "5.0"
            })

        if device.open_device():
            base_response.update({
                "success": True,
                "status": "conectado",
                "status_code": "CONNECTED",
                "message": "Dispositivo conectado correctamente",
                "error": None
            })
            base_response["details"]["sensor_info"] = {
                "finger_present": device.is_finger_present(),
                "sensor_active": True
            }

            dev_info = device.get_device_info()
            if dev_info:
                base_response["details"]["device_info"].update({
                    "model": get_device_model_name(dev_info.wPixelSizeX, dev_info.wPixelSizeY),
                    "resolution": f"{dev_info.wPixelSizeX}x{dev_info.wPixelSizeY}"
                })

            serial = device.get_serial_number()
            if serial:
                base_response["details"]["device_info"]["serial_number"] = serial
        else:
            base_response.update({
                "status": "error",
                "status_code": "DEVICE_BUSY",
                "message": "Dispositivo ocupado o no disponible",
                "error": "Puede estar en uso por otra aplicación"
            })
    except Exception as e:
        base_response.update({
            "status": "error",
            "status_code": "EXECUTION_ERROR",
            "message": "Fallo en la ejecución del script",
            "error": str(e)
        })
    finally:
        device.close_device()

    return json.dumps(base_response, indent=2, ensure_ascii=False)

if __name__ == "__main__":
    try:
        # Obtener el estado como diccionario
        device_status = json.loads(get_device_status())
        
        # Limpiar la salida para Laravel
        sys.stdout.buffer.write(
            json.dumps(device_status, ensure_ascii=False).encode('utf-8')
        )
        sys.exit(0)
        
    except Exception as e:
        error_response = {
            "success": False,
            "error": str(e),
            "timestamp": datetime.now().isoformat(),
            "status": "error",
            "status_code": "SCRIPT_FAILURE"
        }
        sys.stdout.buffer.write(
            json.dumps(error_response, ensure_ascii=False).encode('utf-8')
        )
        sys.exit(1)
