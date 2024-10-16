import apa102
import time
import threading
import syslogger
from gpiozero import LED
try:
    import queue as Queue
except ImportError:
    import Queue as Queue


log = syslogger.Syslogger()

class Leds:
    PIXELS_N = 12

    def __init__(self):
        self.dev = apa102.APA102(num_led=self.PIXELS_N)

        self.power = LED(5)
        self.power.on()

        self.queue = Queue.Queue()
        self.thread = threading.Thread(target=self._run)
        self.thread.daemon = True
        self.thread.start()


    
    
    def _run(self):
        while True:
            func = self.queue.get()
            self.pattern.stop = False
            func()

    def show(self, r, g, b, br, half=False):
        try:
            if half==False:
                step=1
            else:
                step=2
                for i in range(0,self.PIXELS_N):
                    self.dev.set_pixel(i, 0, 0, 0, 0)

            for i in range(0,self.PIXELS_N,step):
                self.dev.set_pixel(i, r, g, b, br)

            self.dev.show()
            print("r:" + str(r) + " g:" + str(g) + " b:" + str(b) + " br:" + str(br) + " half:" + str(half))
        except Exception as err:
            print(repr(err))
            log.log(str(repr(err)), 'led.show')


    def wakeup(self):
        count=0
        pos=0
        while(count<6):
            try:
                for i in range(0,self.PIXELS_N):
                    self.dev.set_pixel(i, 0, 0, 0, 0)
                
                self.dev.set_pixel(pos, 255, 0, 0, 50)
                self.dev.set_pixel(pos+3, 0, 255, 0, 50)
                self.dev.set_pixel(pos+6, 0, 0, 255, 50)
                self.dev.set_pixel(pos+9, 255, 0, 255, 50)
                count=count + 1
                pos=pos+1
                if pos==3:
                    pos=0
                self.dev.show()
                time.sleep(0.1)
            except Exception as err:
                count=6
                print(repr(err))
                log.log(str(repr(err)), 'led.wakeup')    


         
    


