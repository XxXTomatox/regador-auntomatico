#include <DHT.h>
#include <DHT_U.h>
#include "HX711.h"

// DHT22(sensor temperatura,humedad e el ambiente )
#define Type DHT22
int dhtPin = 2;
DHT HT(dhtPin, Type);

//SENSOR DE TIERRA (sensor de humedad de tierra)
#define sensorTierra A0
#define motorPin 10

const int UMBRAL_ENCENDER = 35;  // Si baja de 35% → enciende bomba
const int UMBRAL_APAGAR   = 45;  // Si sube de 45% → apaga bomba

bool bombaEncendida = false;

//HX711 (sensor de peso)
const int LOADCELL_DOUT_PIN = 6;
const int LOADCELL_SCK_PIN  = 5;
HX711 scale;

// Factor de calibración ya obtenido para el sensor de peso 
const float FACTOR_CALIBRACION = -209.9429;

void setup() {
  Serial.begin(9600);
  HT.begin();

  pinMode(motorPin, OUTPUT);
  digitalWrite(motorPin, LOW);  // Bomba apagada al iniciar

  //Inicializar báscula
  scale.begin(LOADCELL_DOUT_PIN, LOADCELL_SCK_PIN);
  Serial.println("Iniciando báscula...");
  delay(1000);

  scale.set_scale(FACTOR_CALIBRACION);

  Serial.println("Retira todo peso. Tarando...");
  delay(2000);
  scale.tare();
  Serial.println("Báscula lista.");

  delay(2000);  // Esperar a que el DHT22 se estabilice
}

//FUNCIONES AUXILIARES 

// Lee y muestra temperatura y humedad ambiente
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

// Lee el sensor de tierra y controla la bomba
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

// Lee y muestra el peso de la báscula
void leerBascula() {
  if (scale.is_ready()) {
    float peso = scale.get_units(10);
    peso = abs(peso);            // Quitar signo negativo
    if (peso < 1.0) peso = 0;    // Filtro de ruido(que no barien mucho el peso)

    Serial.print("Peso: ");
    Serial.print(peso, 1);
    Serial.println(" g");
  } else {
    Serial.println("HX711 no responde. Revisa conexiones.");
  }
}

// LOOP 
void loop() {
  //se mandan a llamar las funciones y imprime todo en la terminal para que quede ordenado 
  Serial.println("======= LECTURAS =======");

  leerDHT22();
  controlBomba();
  leerBascula();

  Serial.println("-----------------------");
  delay(2000);
}