from flask import Flask, Response, send_file
from ultralytics import YOLO
import cv2
import threading
import os
import time
from datetime import datetime

app = Flask(__name__)

model = YOLO("yolov8n.pt")
LABELS = model.model.names

stream_url = "http://10.30.94.182:5000/PALFAT" #raspberrypi ip
cap = cv2.VideoCapture(stream_url)

snapshot_dir = "snapshots"
os.makedirs(snapshot_dir, exist_ok=True)

latest_frame_path = os.path.join(snapshot_dir, "detected.jpg")

current_frame = None
last_detections = []
last_saved_time = 0
lock = threading.Lock()

frame_skip = 2
min_area = 3000
save_interval = 5

def detect_objects():
    global current_frame, last_detections, last_saved_time, latest_frame_path
    frame_count = 0

    while True:
        ret, frame = cap.read()
        if not ret:
            continue

        frame = cv2.resize(frame, (640, 480))
        clean_frame = frame.copy()
        frame_count += 1

        if frame_count % frame_skip == 0:
            results = model.predict(frame, conf=0.5, classes=[0, 15, 16], verbose=False)
            boxes = results[0].boxes
            last_detections = []

            if boxes is not None and len(boxes) > 0:
                for box in boxes:
                    x1, y1, x2, y2 = map(int, box.xyxy[0])
                    cls_id = int(box.cls[0])
                    label = LABELS.get(cls_id, "object")

                    area = (x2 - x1) * (y2 - y1)
                    if area < min_area:
                        continue

                    last_detections.append((x1, y1, x2, y2, label))

                if time.time() - last_saved_time > save_interval:
                    # File name: 19072025_120102
                    timestamp_for_file = datetime.now().strftime("%d%m%Y_%H%M%S")
                    filename = f"detected_{timestamp_for_file}.jpg"
                    latest_frame_path = os.path.join(snapshot_dir, filename)

                    # Image timestamp text: "Date : 19/07/2025 Time : 12/01/02"
                    timestamp_for_img = datetime.now().strftime("Date : %d/%m/%Y Time : %H:%M:%S TEAM DSY")

                    cv2.putText(clean_frame, timestamp_for_img, (10, clean_frame.shape[0] - 10),
                                cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)

                    cv2.imwrite(latest_frame_path, clean_frame)
                    last_saved_time = time.time()

        for x1, y1, x2, y2, label in last_detections:
            cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)
            cv2.putText(frame, label, (x1, y1 - 10),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 255, 0), 2)

        with lock:
            current_frame = frame

def generate_stream():
    global current_frame
    while True:
        with lock:
            if current_frame is None:
                continue
            ret, buffer = cv2.imencode('.jpg', current_frame)
            frame = buffer.tobytes()

        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame + b'\r\n')

@app.route("/live")
def video_feed():
    return Response(generate_stream(),
                    mimetype="multipart/x-mixed-replace; boundary=frame")

@app.route("/latest")
def get_latest_frame():
    if os.path.exists(latest_frame_path):
        return send_file(latest_frame_path, mimetype='image/jpeg')
    else:
        return "No detection yet", 404

if __name__ == "__main__":
    threading.Thread(target=detect_objects, daemon=True).start()
    app.run(host="0.0.0.0", port=5001, debug=False, threaded=True)
