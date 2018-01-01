Temperature and Humidity Monitor
================================

![Device](/device.jpg?raw=true)

## Description:

These are the PCB design files and source code for my ESP8266 based temperature and humidity monitor.

The [board](/board) directory contains the KiCAD schematic and PCB layout files together with the [gerber files](/board/gerbers). While the [firmware](/firmware) directory, contains the Arduino source code for the ESP8266. The [server](/server) directory contains the server side script that logs the received data, as well as an SQL file for creating the database tables. Finally, the design files for the enclosure can be found on [Thingiverse](https://www.thingiverse.com/thing:2740731).

## Programming Instructions:

The firmware can be compiled and burned to an ESP8266 microcontroller using the [Arduino IDE](https://www.arduino.cc/en/Main/Software). To compile the project code you'll also need to install the [SimpleDHT](https://github.com/winlinvip/SimpleDHT/) library package using the [Library Manager](https://www.arduino.cc/en/Guide/Libraries#toc2) of the Arduino IDE. Additionally, for programming the ESP8266 using the Arduino IDE, the [ESP8266 Community](https://github.com/esp8266/Arduino) core package needs to be installed using the [Board Manager](https://www.arduino.cc/en/Guide/Cores).

## License:

The design files for the PCB and the enclosure are all distributed under the [Attribution-ShareAlike 4.0 International](https://creativecommons.org/licenses/by-sa/4.0/) license. The source code for the firmware and the server are distributed under the MIT license.