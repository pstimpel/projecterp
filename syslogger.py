import syslog

class Syslogger:
    def __init__(self):
        self.facility = syslog.LOG_SYSLOG
        syslog.openlog(ident="ERPCLIENT",logoption=syslog.LOG_PID, facility=syslog.LOG_SYSLOG)


    def log(self, text, source):    
        try:
            syslog.syslog(source + ': ' + text)
            #print("Log done")
        finally:
            pass


