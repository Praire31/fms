#include <WiFi.h>
#include <HTTPClient.h>
#include <Adafruit_Fingerprint.h>
#include <HardwareSerial.h>

// ---------------- WIFI ----------------
const char* ssid = "YANACORP-4G";
const char* password = "yana.africa2024";

// ---------------- Fingerprint ----------------
HardwareSerial mySerial(2); // RX=16, TX=17
Adafruit_Fingerprint finger(&mySerial);

int id = 0; // ID for enrollment

// ---------------- Laravel API ----------------
// IMPORTANT: make sure your Laravel route matches this URL
String serverUrl = "http://192.168.1.158:8000/fingerprint/attendance?user_id=";

// Mode selector
enum Mode { NONE, ENROLL, RECOGNITION };
Mode currentMode = NONE;

void setup() {
  Serial.begin(115200);
  delay(100);

  // Connect to WiFi
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\n✅ WiFi connected!");
  Serial.print("ESP32 IP Address: ");
  Serial.println(WiFi.localIP());

  // Initialize fingerprint sensor
  mySerial.begin(57600, SERIAL_8N1, 16, 17);
  finger.begin(57600);

  if (finger.verifyPassword()) {
    Serial.println("✅ Fingerprint sensor detected!");
  } else {
    Serial.println("❌ Sensor not found, check wiring/baud.");
    while (1) { delay(1); }
  }

  finger.getParameters();
  finger.getTemplateCount();
  Serial.print("Sensor contains ");
  Serial.print(finger.templateCount);
  Serial.println(" templates");

  Serial.println("\nSelect mode: Type E for Enrollment, R for Recognition");
}

void loop() {
  // Check for user input for mode selection
  if (Serial.available()) {
    char mode = Serial.read();
    Serial.read(); // clear newline

    if (mode == 'E' || mode == 'e') {
      currentMode = ENROLL;
      Serial.println("\n--- ENROLLMENT MODE ---");
      enrollMode();
      // After enrollment, switch automatically to recognition
      currentMode = RECOGNITION;
      Serial.println("\n--- SWITCHING TO RECOGNITION MODE ---");
      Serial.println("Waiting for a finger...");
    } else if (mode == 'R' || mode == 'r') {
      currentMode = RECOGNITION;
      Serial.println("\n--- RECOGNITION MODE ---");
      Serial.println("Waiting for a finger...");
    } else {
      Serial.println("Invalid option. Type E or R.");
    }
  }

  // Recognition mode loop
  if (currentMode == RECOGNITION) {
    int fingerID = getFingerprintIDez(); // -1 if no match
    if (fingerID >= 0) {
      Serial.print("Finger matched! ID: ");
      Serial.println(fingerID);
      sendAttendance(fingerID);   // <-- sends to Laravel
      delay(3000); // avoid duplicate logging
    }
  }
}

// ---------------- Enrollment Mode ----------------
void enrollMode() {
  Serial.println("Type in the ID # (1-127) for this finger:");

  while (!Serial.available());
  id = Serial.parseInt();

  // Clear leftover input
  while (Serial.available() > 0) Serial.read();

  Serial.print("Enrolling ID #");
  Serial.println(id);

  uint8_t result = enrollFingerprint(id);

  if (result == FINGERPRINT_OK) {
    Serial.println("🎉 Enrollment successful!");
    Serial.println("Now add the user to your database with fingerprint_id = " + String(id));
  } else {
    Serial.println("⚠️ Enrollment failed, try again.");
  }
}

// ---------------- Fingerprint Enrollment Routine ----------------
uint8_t enrollFingerprint(uint8_t id) {
  int p = -1;

  // First scan
  Serial.println("Place finger on sensor...");
  while (p != FINGERPRINT_OK) {
    p = finger.getImage();
    if (p == FINGERPRINT_NOFINGER) continue;
    if (p != FINGERPRINT_OK) {
      Serial.println("❌ Error capturing image, try again...");
      delay(500);
    }
  }

  p = finger.image2Tz(1);
  if (p != FINGERPRINT_OK) return p;

  Serial.println("✅ First scan complete. Remove finger.");
  delay(2000);

  // Second scan
  Serial.println("Place the SAME finger again...");
  p = -1;
  while (p != FINGERPRINT_OK) {
    p = finger.getImage();
    if (p == FINGERPRINT_NOFINGER) continue;
    if (p != FINGERPRINT_OK) {
      Serial.println("❌ Error capturing image, try again...");
      delay(500);
    }
  }

  p = finger.image2Tz(2);
  if (p != FINGERPRINT_OK) return p;

  // Create model and store
  p = finger.createModel();
  if (p != FINGERPRINT_OK) return p;
  p = finger.storeModel(id);
  if (p != FINGERPRINT_OK) return p;

  return FINGERPRINT_OK;
}

// ---------------- Fingerprint Detection ----------------
int getFingerprintIDez() {
  uint8_t p = finger.getImage();
  if (p != FINGERPRINT_OK) return -1;

  p = finger.image2Tz();
  if (p != FINGERPRINT_OK) return -1;

  p = finger.fingerFastSearch();
  if (p != FINGERPRINT_OK) return -1;

  Serial.print("Found ID #"); Serial.print(finger.fingerID);
  Serial.print(" with confidence of "); Serial.println(finger.confidence);
  return finger.fingerID;
}

// ---------------- Send Attendance to Laravel ----------------
void sendAttendance(int userID) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠️ WiFi not connected");
    return;
  }

  HTTPClient http;
  String url = serverUrl + String(userID);
  Serial.println("Requesting: " + url);

  http.begin(url);
  http.setTimeout(5000); // 5s timeout
  int httpCode = http.GET();

  if (httpCode > 0) {
    String payload = http.getString();
    Serial.println("📩 Server Response: " + payload);
  } else {
    Serial.print("⚠️ HTTP request failed, code: ");
    Serial.println(httpCode);
  }

  http.end();
}
