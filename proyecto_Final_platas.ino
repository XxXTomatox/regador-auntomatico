#include <DHT.h>
#include <DHT_U.h>
#include "HX711.h"

#define Type DHT22
int dhtPin = 2;
DHT HT(dhtPin, Type);

#define sensorTierra A0
#define motorPin 10

const int UMBRAL_ENCENDER = 35;
const int UMBRAL_APAGAR   = 45; 

bool bombaEncendida = false;

const int LOADCELL_DOUT_PIN = 6;
const int LOADCELL_SCK_PIN  = 5;
HX711 scale;

const float FACTOR_CALIBRACION = -209.9429;

void setup() {
  Serial.begin(9600);
  HT.begin();

  pinMode(motorPin, OUTPUT);
  digitalWrite(motorPin, LOW); 

  scale.begin(LOADCELL_DOUT_PIN, LOADCELL_SCK_PIN);
  Serial.println("Iniciando báscula...");
  delay(1000);

  scale.set_scale(FACTOR_CALIBRACION);
  Serial.println("Retira todo peso. Tarando...");
  delay(2000);
  scale.tare();
  Serial.println("Báscula lista.");
  delay(2000);

}

void leerDHT22() {
  float celsius = HT.readTemperature();
  float humedadAire = HT.readHumidity();

  if (isnan(celsius) || isnan(humedadAire)) {
    Serial.println("Error al leer el sensor DHT22");
  } else {
    Serial.print("Temperatura: ");
    Serial.print(celsius);
    Serial.println(" °C");

    Serial.print("Humedad ambiente: ");
    Serial.print(humedadAire);
    Serial.println(" %");
  }
}

void controlBomba() {
  int humedadTierra = map(analogRead(sensorTierra), 0, 1023, 100, 0);
  humedadTierra = constrain(humedadTierra, 0, 100);

  Serial.print("Humedad tierra: ");
  Serial.print(humedadTierra);
  Serial.println(" %");

  if (!bombaEncendida && humedadTierra < UMBRAL_ENCENDER) {
    digitalWrite(motorPin, HIGH);
    bombaEncendida = true;
    Serial.println(">>> Bomba ENCENDIDA (tierra seca)");
  }
  else if (bombaEncendida && humedadTierra > UMBRAL_APAGAR) {
    digitalWrite(motorPin, LOW);
    bombaEncendida = false;
    Serial.println(">>> Bomba APAGADA (tierra húmeda)");
  }

  Serial.print("Estado bomba: ");
  Serial.println(bombaEncendida ? "ENCENDIDA" : "APAGADA");
}

void leerBascula() {
  if (scale.is_ready()) {
    float peso = scale.get_units(10);
    peso = abs(peso);
    if (peso < 1.0) peso = 0;

    Serial.print("Peso: ");
    Serial.print(peso, 1);
    Serial.println(" g");
  } else {
    Serial.println("HX711 no responde. Revisa conexiones.");
  }
}

void loop() {
  Serial.println("======= LECTURAS =======");

  leerDHT22();
  controlBomba();
  leerBascula();

  Serial.println("-----------------------");
  delay(2000);
}