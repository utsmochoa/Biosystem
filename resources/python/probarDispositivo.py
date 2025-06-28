import ctypes
import threading
import time
from tkinter import Tk, Label, Button, Frame, BOTH
from PIL import Image, ImageTk

# Cargar la DLL
ftr = ctypes.windll.LoadLibrary("ftrScanAPI.dll")
ftr.ftrScanOpenDevice.restype = ctypes.c_void_p
ftr.ftrScanCloseDevice.argtypes = [ctypes.c_void_p]
ftr.ftrScanGetImageSize.argtypes = [ctypes.c_void_p, ctypes.c_void_p]
ftr.ftrScanGetFrame.argtypes = [ctypes.c_void_p, ctypes.c_void_p, ctypes.c_void_p]
ftr.ftrScanIsFingerPresent.argtypes = [ctypes.c_void_p, ctypes.c_void_p]
ftr.ftrScanIsFingerPresent.restype = ctypes.c_bool

# Estructura de tamaño
class FTRSCAN_IMAGE_SIZE(ctypes.Structure):
    _fields_ = [("nWidth", ctypes.c_int),
                ("nHeight", ctypes.c_int),
                ("nImageSize", ctypes.c_int)]

# Conectar lector
hDevice = ftr.ftrScanOpenDevice()
if not hDevice:
    raise Exception("No se pudo abrir el lector")

img_size = FTRSCAN_IMAGE_SIZE()
if not ftr.ftrScanGetImageSize(hDevice, ctypes.byref(img_size)):
    ftr.ftrScanCloseDevice(hDevice)
    raise Exception("No se pudo obtener el tamaño de imagen")

buffer = (ctypes.c_ubyte * img_size.nImageSize)()

# Crear ventana principal
root = Tk()
root.title("Probar dispositivo biométrico")
root.geometry("450x520")
root.resizable(False, False)
root.configure(bg="#bfdbfe")  # Tailwind: bg-blue-200
root.attributes("-topmost", True)

# Centrar ventana
root.update_idletasks()
w = 450
h = 520
x = (root.winfo_screenwidth() - w) // 2
y = (root.winfo_screenheight() - h) // 2
root.geometry(f"{w}x{h}+{x}+{y}")

# Contenedor tipo "card"
card = Frame(root, bg="white", bd=0, highlightthickness=0)
card.place(relx=0.5, rely=0.5, anchor="center", width=400, height=460)

# Logo (si tuvieras uno puedes usar `Image.open(...)`)
title = Label(card, text="Configuración de dispositivo biométrico",
              bg="white", fg="#1e3a8a",  # Tailwind: text-blue-800
              font=("Arial", 14, "bold"), wraplength=380, justify="center")
title.pack(pady=(20, 10))

sub = Label(card, text="Coloca tu dedo en el sensor para probar el lector",
            bg="white", fg="gray", font=("Arial", 10), wraplength=360, justify="center")
sub.pack()

# Imagen de huella
image_label = Label(card, bg="white")
image_label.pack(pady=10)

# Estado de captura
estado_label = Label(card, text="Esperando dedo...", bg="#bfdbfe",
                     fg="#1d4ed8", font=("Arial", 10, "bold"), pady=5)
estado_label.pack(pady=5)

# Botón
btn = Button(card, text="Finalizar prueba", command=root.destroy,
             font=("Arial", 11, "bold"), bg="#1d4ed8", fg="white",
             activebackground="#1e40af", activeforeground="white",
             relief="flat", padx=10, pady=5)
btn.pack(pady=15)

# Lógica de captura
def loop_captura():
    while True:
        dedo = ftr.ftrScanIsFingerPresent(hDevice, None)
        if dedo:
            estado_label.config(text="Dedo detectado ✔", fg="green", bg="#d1fae5")  # bg-green-100
            if ftr.ftrScanGetFrame(hDevice, buffer, None):
                img = Image.frombytes("L", (img_size.nWidth, img_size.nHeight), bytes(buffer))
                img = img.resize((250, 250))
                img = img.transpose(Image.FLIP_LEFT_RIGHT)
                imgtk = ImageTk.PhotoImage(img)
                image_label.config(image=imgtk)
                image_label.image = imgtk
        else:
            estado_label.config(text="Esperando dedo...", fg="#1d4ed8", bg="#bfdbfe")

        time.sleep(0.2)

# Hilo
t = threading.Thread(target=loop_captura, daemon=True)
t.start()

# Cierre correcto
def cerrar():
    ftr.ftrScanCloseDevice(hDevice)
    root.destroy()

root.protocol("WM_DELETE_WINDOW", cerrar)
root.mainloop()
