/*
* Copyright (c) 2017 Manolis Agkopian
* See the file LICENSE for copying permission.
*/

#include <Arduino.h>

#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266HTTPClient.h>

#include <LiquidCrystal.h>
#include <SimpleDHT.h>

ESP8266WiFiMulti WiFiMulti;

const int rs = 4, en = 2, d4 = 13, d5 = 12, d6 = 14, d7 = 16;
LiquidCrystal lcd(rs, en, d4, d5, d6, d7);

int DHT11 = 5;
SimpleDHT11 dht;

void setup() {

  // Configure LCD
  lcd.begin(16, 4);

  // Configure WiFi
  WiFiMulti.addAP("[SSID]", "[Password]");

}

void loop() {
  
  byte temperature = 0;
  byte humidity = 0;
  char postData[100] = {0};

  // Sample rate is set to 1 Hz
  delay(1000);

  // Read the sensor
  if ( dht.read(DHT11, &temperature, &humidity, NULL) != SimpleDHTErrSuccess ) {
    lcd.setCursor(0, 1);
    lcd.print("Sensor Failure"); // In case of error output sensor failure and return
    return;
  }

  // Clear the sensor failure error message
  lcd.setCursor(0, 1);
  lcd.print("              ");

  // Output the temperature
  lcd.setCursor(0, 2);
  lcd.print("Temperature: ");
  lcd.print((int) temperature);
  lcd.print("C");

  // Output the humidity
  lcd.setCursor(0, 3);
  lcd.print("Humidity: ");
  lcd.print((int) humidity);
  lcd.print("%");

  sprintf(postData, "{\"client_id\":\"[Client ID]\",\"client_key\":\"[Client Key]\",\"temperature\":\"%d\",\"humidity\":\"%d\"}", (int) temperature, (int) humidity);
  
  // If connected to the WiFi send the mesurment to the server
  if((WiFiMulti.run() == WL_CONNECTED)) {
    
    HTTPClient http;

    // Set request URL
    http.begin("http://[Host]/log.php"); // For HTTPS connection the fingerprint of the certificate needs to be supplied as a second parameter

    // Start connection and send HTTP headers
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept", "application/json");
    int httpCode = http.POST(postData);

    if( httpCode > 0 && httpCode == HTTP_CODE_OK ) {
      String payload = http.getString();
      lcd.setCursor(0, 0);
      lcd.print(payload);
    }
    else {
      String error = http.errorToString(httpCode).c_str();
    }

    http.end();
    
  }
  
}

