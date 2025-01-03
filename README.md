# THIS IS JUST A PROOF OF CONCEPT
# DO NOT USE THIS SPAGHETTI CODE FOR ANYTHING IMPORTANT
# I AM IN NO WAY LIABLE FOR ANY HARM THIS CODE MAY CAUSE

# What this does

This is a basic ERP system controlled via voice commands and/or a small web interface.

# What is needed

- Raspberry Pi
- SEEEDStudio 4 Mic Array Hat
- A web server in your local network to run the PHP components, accessible by the Raspberry Pi
- A PostgreSQL server for the few tables required by the web interface and API
- A switch button and a resistor to trigger voice input on the Raspberry Pi (I dislike wake words)
- A good attitude to deal with the messiness of this code
- A USB drive for storing temporary audio files

# How it works

A web server runs the web interface and API, both storing their data in the PostgreSQL database. The Pi communicates with the API and Google. When you press the switch button, the mic starts listening to your voice. It uses Google's Speech-to-Text to convert speech into text, which is then sent to the API. The API processes the text and responds, with the response being converted to speech using Google's Text-to-Speech. That's basically the whole process.

# But how does it really work?

When you press the switch button (connected to a GPIO pin on the Pi), some GPIOs are routed through the mic array hat, so they remain accessible even when the Pi hat is in place. This triggers the Python code, which lights up some LEDs on the mic hat and starts listening for voice input. A simple command like "Hilfe" (help) would work. Yes, the proof of concept is built for a German speaker. After you stop talking, the audio is converted to text by Google's Speech-to-Text. And yes, you'll need a Google account. And yes, excessive use will cost some money. And yes, you can't use it without providing your credit card info. But hey, poor people need our support. The returned text is sent to the web API, which processes whether it was a command, description, or something else. The API sends the result back to the Pi as text, and the Pi converts it into speech using Google's Text-to-Speech.

An example interaction would be:

You: Add article resistor 200 ohms 0.25 watt 1% 0802

Pi: Article found, please provide quantity

You: 26

Pi: OK, which storage location?

You: Box 19

Pi: OK, article resistor 200 ohms 0.25 watt 1% 0802, quantity 26, added to box 19.

If the API cannot find the article, it will ask if it should add the article to the database. If the storage location isn’t found, it will ask if it should create the storage location.

Sometimes, Google struggles with certain translations, especially technical terms. For example, "Hertz" (frequency) and the German word for "heart" (Herz) sound alike. So I added a hardcoded translation table for such cases to ensure my items in stock have the correct descriptions.

The web interface uses the same API, so you can do the same things via the web interface as you can with voice commands. This was useful for debugging.

# Using AI

Based on a chat with Andreas Spiess I was adding some AI to the game. Please see setup.md and basictalk.py for more details. You could change the basic prompt as well to talk in your language, or to have more specific results.

But we did it, we made the solution SUPERSMART by adding hyped AI to it. Hyper hyper ...

# How to proceed

**DO NOT USE THIS CODE FOR ANYTHING OTHER THAN INSPIRATION**

Honestly, there's so much garbage in here, don't even think about just changing a few variables and settings to make it work. That's why I haven't provided any setup instructions. However, I did include some of the links I used while figuring out how to get this working. Please refer to `setup.md`.

# Credits

- Seon, the Unexpected Maker, for the basic trigger idea. He showcased something similar in one of his videos: https://www.youtube.com/watch?v=UTIY8jajxKw
- The developers of https://github.com/PHPMailer/PHPMailer/
- Martin Erzberger for the APA102 LED code
- Andreas Spiess for encouraging me to publish this in a discussion under one of his great videos: https://www.youtube.com/watch?v=9pl1tvPCBE0
- And all the other wonderful people out there who provide software, tools, and resources for free, allowing me to do what I enjoy.
