import smtplib
import syslogger

from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

log = syslogger.Syslogger()

class Mail:
    def __init__(self):
        # TODO: change mail addresses
        self.fromaddr = "xxx@yyy"
        self.toaddr = "yyyl@xxx"

    def send(self, subject, body):

        msg = MIMEMultipart()
        msg['From'] = self.fromaddr
        msg['To'] = self.toaddr
        msg['Subject'] = subject
        msg.attach(MIMEText(body, 'plain'))
        try:
                # TODO: change mailserver
                server = smtplib.SMTP('a.b.c.d', 25)
                #server.starttls(
                #server.login(fromaddr, "FromUserPassword")
                text = msg.as_string()
                server.sendmail(self.fromaddr, self.toaddr, text)
                server.quit()
                log.log('Mail sent', 'mail')
        except Exception as err:
                print(repr(err))
                log.log(str(repr(err)), 'mail')
                
