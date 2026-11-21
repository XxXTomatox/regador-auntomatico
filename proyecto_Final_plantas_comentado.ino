#include <DHT.h>
#include "HX711.h"

#define Type DHT22
int dhtPin = 2;
DHT HT(dhtPin, Type);

const float UMBRAL_TEMPERATURA = 45.0;

const int pinSensorHumedad = A0;
const int pinMotor = 10;
const int umbralHumedad = 45;

int valorSensor = 0;
int porcentajeHumedad = 0;

bool alertaTermica = false;

const int LOADCELL_DOUT_PIN = 6;
const int LOADCELL_SCK_PIN  = 5;
HX711 scale;
const float FACTOR_CALIBRACION = -209.9429;

// ==== NUEVO: umbral de peso para aviso de llenado ====
const float UMBRAL_PESO_BAJO = 25.0; // gramos

void setup() {
  Serial.begin(9600);
  HT.begin();

  pinMode(pinMotor, OUTPUT);
  digitalWrite(pinMotor, LOW);

  Serial.println("Sistema de riego iniciado");
  Serial.print("Umbral humedad: "); Serial.print(umbralHumedad); Serial.println("%");
  Serial.print("Umbral temperatura: "); Serial.print(UMBRAL_TEMPERATURA); Serial.println(" C");

  scale.begin(LOADCELL_DOUT_PIN, LOADCELL_SCK_PIN);
  Serial.println("Iniciando bascula...");
  delay(1000);
  scale.set_scale(FACTOR_CALIBRACION);
  Serial.println("Retira todo peso. Tarando...");
  delay(2000);
  scale.tare();
  Serial.println("Bascula lista.");
  delay(2000);
}

// ==== LECTURA DE TEMPERATURA ====
float leerTemperatura() {
  float temperatura = HT.readTemperature();

  if (isnan(temperatura)) {
    Serial.println("Error al leer el sensor DHT22");
    return NAN;
  }

  Serial.print("Temperatura: ");
  Serial.print(temperatura);
  Serial.println(" C");

  float humedadAire = HT.readHumidity();
  if (!isnan(humedadAire)) {
    Serial.print("Humedad ambiente: ");
    Serial.print(humedadAire);
    Serial.println(" %");
  }

  return temperatura;
}

// ==== VERIFICACION DE ALERTA TERMICA ====
void verificarTemperatura(float temperatura) {
  if (!isnan(temperatura)) {
    alertaTermica = (temperatura >= UMBRAL_TEMPERATURA);
  }

  if (alertaTermica) {
    Serial.print(">> Temperatura ALTA");
    if (!isnan(temperatura)) {
      Serial.print(" (");
      Serial.print(temperatura);
      Serial.print(" C)");
    } else {
      Serial.print(" (sensor NAN)");
    }
    Serial.println(" -> Motor forzado ENCENDIDO");
  } else {
    Serial.println(">> Temperatura normal");
  }
}

// ==== CONTROL DE LA BOMBA ====
void controlBomba() {
  valorSensor = analogRead(pinSensorHumedad);
  porcentajeHumedad = map(valorSensor, 1023, 0, 0, 100);
  porcentajeHumedad = constrain(porcentajeHumedad, 0, 100);

  Serial.print("Valor sensor: "); Serial.print(valorSensor);
  Serial.print(" | Humedad: "); Serial.print(porcentajeHumedad); Serial.print("% -> ");

  if (alertaTermica) {
    digitalWrite(pinMotor, HIGH);
    Serial.println("Motor ENCENDIDO (por temperatura alta, humedad ignorada)");
  }
  else if (porcentajeHumedad > umbralHumedad) {
    digitalWrite(pinMotor, HIGH);
    Serial.println("Motor apagado (por humedad)");
  }
  else {
    digitalWrite(pinMotor, LOW);
    Serial.println("Motor encendido");
  }
}

// ==== LECTURA DE LA BASCULA (con alerta de llenado) ====
void leerBascula() {
  if (scale.is_ready()) {
    float peso = scale.get_units(10);
    peso = abs(peso);
    if (peso < 1.0) peso = 0;
    Serial.print("Peso: "); Serial.print(peso, 1); Serial.println(" g");

    // ==== NUEVO: alerta si el peso está por debajo del umbral ====
    if (peso < UMBRAL_PESO_BAJO) {
      Serial.println(">>> ALERTA: LLENAR DE AGUA (peso bajo) <<<");
    }
  } else {
    Serial.println("HX711 no responde. Revisa conexiones.");
  }
}

void loop() {
  Serial.println("======= LECTURAS =======");
  float temperatura = leerTemperatura();
  verificarTemperatura(temperatura);
  controlBomba();
  leerBascula();
  Serial.println("-----------------------");
  delay(2000);
}
