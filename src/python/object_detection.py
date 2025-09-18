# from flask import Flask, Response, send_file, request, jsonify
# from ultralytics import YOLO
# import cv2
# import threading
# import os
# import time
# from datetime import datetime
# import mysql.connector
# import io
# import requests

# app = Flask(__name__)

# # Load YOLO model
# model = YOLO("yolov8n.pt")
# LABELS = model.model.names

# # Snapshot storage folder
# snapshot_dir = "../upload/capture"
# os.makedirs(snapshot_dir, exist_ok=True)

# # Globals
# current_frame = None
# last_detections = []
# last_saved_time = 0
# lock = threading.Lock()
# frame_skip = 2
# min_area = 3000
# save_interval = 5  # seconds
# last_email_sent_time = 0
# email_interval = 30  # seconds

# # MySQL config
# DB_HOST = 'localhost'
# DB_USER = 'root'
# DB_PASSWORD = ''
# DB_NAME = 'protecting_agriculture_land_form_thef_animal'

# # Runtime globals
# detection_started = False
# current_user_id = None
# cap = None

# def get_user_stream_link(user_id):
#     try:
#         conn = mysql.connector.connect(
#             host=DB_HOST,
#             user=DB_USER,
#             password=DB_PASSWORD,
#             database=DB_NAME
#         )
#         cursor = conn.cursor()
#         cursor.execute("SELECT stream_link FROM user_profile WHERE id = %s", (user_id,))
#         result = cursor.fetchone()
#         cursor.close()
#         conn.close()

#         if result and result[0]:
#             return result[0]
#         else:
#             print(f"[ERROR] No stream link found for user_id {user_id}")
#             return None
#     except Exception as e:
#         print(f"[DB ERROR] Failed to fetch stream link: {e}")
#         return None

# def insert_captured_image_to_db(image_path):
#     global current_user_id, last_email_sent_time

#     if not current_user_id:
#         print("[ERROR] No user_id set for this request")
#         return

#     table_name = f"captured_images_user_{current_user_id}"

#     try:
#         with open(image_path, "rb") as f:
#             img_blob = f.read()

#         conn = mysql.connector.connect(
#             host=DB_HOST,
#             user=DB_USER,
#             password=DB_PASSWORD,
#             database=DB_NAME
#         )
#         cursor = conn.cursor()

#         sql = f"INSERT INTO `{table_name}` (image_path, captured_image, captured_at) VALUES (%s, %s, NOW())"
#         relative_path = os.path.relpath(image_path, start=os.path.dirname(snapshot_dir))
#         cursor.execute(sql, (relative_path, img_blob))

#         conn.commit()
#         cursor.close()
#         conn.close()
#         print(f"[DB] Inserted image with blob into {table_name}: {relative_path}")

#         now = time.time()
#         if now - last_email_sent_time >= email_interval:
#             try:
#                 php_url = "http://localhost/Web Project/Protecting-Agriculture-Land-From-Theft-and-Animal/src/php/Send_Image_Email.php"
#                 post_data = {"user_id": current_user_id}
#                 response = requests.post(php_url, data=post_data)
#                 print(f"[EMAIL] PHP Response: {response.text}")
#                 last_email_sent_time = now  
#             except Exception as e:
#                 print(f"[EMAIL ERROR] {e}")
#         else:
#             print(f"[EMAIL] Skipped: Waiting {int(email_interval - (now - last_email_sent_time))}s before next email.")

#     except mysql.connector.Error as err:
#         print(f"[DB ERROR] Failed to insert image: {err}")
#     except Exception as e:
#         print(f"[ERROR] {e}")


# def detect_objects():
#     global current_frame, last_detections, last_saved_time, cap
#     frame_count = 0

#     while True:
#         if cap is None:
#             time.sleep(0.2)
#             continue

#    for _ in range(4):  # Flush stale frames
#     cap.grab()
# ret, frame = cap.read()
#         if not ret:
#             print("[WARN] Unable to read frame from stream")
#             time.sleep(0.2)
#             continue

#         frame = cv2.resize(frame, (640, 480))
#         clean_frame = frame.copy()
#         frame_count += 1

#         if frame_count % frame_skip == 0:
#             results = model.predict(frame, conf=0.5, classes=[0, 15, 16], verbose=False)
#             boxes = results[0].boxes
#             last_detections.clear()

#             if boxes is not None and len(boxes) > 0:
#                 for box in boxes:
#                     x1, y1, x2, y2 = map(int, box.xyxy[0])
#                     cls_id = int(box.cls[0])
#                     label = LABELS.get(cls_id, "object")

#                     # 🚨 Send alert to Raspberry Pi if specific objects are detected
#                     if label in ["person", "dog", "cow"]:
#                         try:
#                             alert_url = "http://10.56.242.182:5000/alert"
#                             requests.post(alert_url, data={"type": label})
#                             print(f"[ALERT] Sent alert to Raspberry Pi for {label}")
#                         except Exception as e:
#                             print(f"[ALERT ERROR] Could not send alert: {e}")

#                     area = (x2 - x1) * (y2 - y1)
#                     if area < min_area:
#                         continue

#                     last_detections.append((x1, y1, x2, y2, label))

#                 if time.time() - last_saved_time > save_interval:
#                     timestamp_for_file = datetime.now().strftime("%d%m%Y_%H%M%S")
#                     filename = f"detected_{timestamp_for_file}.jpg"
#                     latest_frame_path = os.path.join(snapshot_dir, filename)

#                     timestamp_for_img = datetime.now().strftime("Date : %d/%m/%Y Time : %I:%M:%S %p TEAM DSY")
#                     cv2.putText(clean_frame, timestamp_for_img, (10, clean_frame.shape[0] - 10),
#                                 cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)

#                     cv2.imwrite(latest_frame_path, clean_frame)
#                     last_saved_time = time.time()

#                     insert_captured_image_to_db(latest_frame_path)

#         for x1, y1, x2, y2, label in last_detections:
#             cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)
#             cv2.putText(frame, label, (x1, y1 - 10),
#                         cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 255, 0), 2)

#         with lock:
#             current_frame = frame


# def generate_stream():
#     global current_frame
#     encode_param = [int(cv2.IMWRITE_JPEG_QUALITY), 70]  # Faster encoding

#     while True:
#         with lock:
#             if current_frame is None:
#                 continue
#             ret, buffer = cv2.imencode('.jpg', current_frame, encode_param)
#             frame = buffer.tobytes()

#         yield (b'--frame\r\n'
#                b'Content-Type: image/jpeg\r\n\r\n' + frame + b'\r\n')


# @app.route("/live")
# def video_feed():
#     global current_user_id, detection_started, cap

#     try:
#         user_id = int(request.args.get("user_id"))
#     except:
#         return "Missing or invalid user_id", 400

#     stream_link = get_user_stream_link(user_id)
#     if not stream_link:
#         return f"No stream link for user {user_id}", 404

#     current_user_id = user_id

#     cap = cv2.VideoCapture(stream_link)
#    cap.set(cv2.CAP_PROP_BUFFERSIZE, 1)

#     if not cap.isOpened():
#         return "Failed to open video stream", 500

#     if not detection_started:
#         threading.Thread(target=detect_objects, daemon=True).start()
#         detection_started = True

#     return Response(generate_stream(),
#                     mimetype="multipart/x-mixed-replace; boundary=frame")


# def fetch_latest_image_from_db(user_id):
#     try:
#         conn = mysql.connector.connect(
#             host=DB_HOST,
#             user=DB_USER,
#             password=DB_PASSWORD,
#             database=DB_NAME
#         )
#         cursor = conn.cursor()
#         table_name = f"captured_images_user_{user_id}"
#         query = f"""
#             SELECT captured_image, captured_at FROM `{table_name}`
#             ORDER BY captured_at DESC LIMIT 1
#         """
#         cursor.execute(query)
#         result = cursor.fetchone()
#         cursor.close()
#         conn.close()
#         if result:
#             return result[0], result[1]
#         else:
#             return None, None
#     except Exception as e:
#         print(f"[DB ERROR] {e}")
#         return None, None


# @app.route("/latest")
# def get_latest_frame():
#     user_id = request.args.get("user_id")
#     if not user_id:
#         return "[ERROR] No user_id set for this request", 400

#     image_blob, captured_at = fetch_latest_image_from_db(user_id)
#     if image_blob:
#         return send_file(
#             io.BytesIO(image_blob),
#             mimetype='image/jpeg',
#             as_attachment=False,
#             download_name=f"latest_{user_id}.jpg"
#         )
#     else:
#         return "No detection yet", 404


# if __name__ == "__main__":
#     app.run(host="0.0.0.0", port=5001, debug=False, threaded=True)


from flask import Flask, Response, send_file, request, jsonify
from ultralytics import YOLO
import cv2
import threading
import os
import time
from datetime import datetime
import mysql.connector
import io
import requests

app = Flask(__name__)

# Load YOLO model
model = YOLO("yolov8n.pt")
LABELS = model.model.names

# Snapshot storage folder
snapshot_dir = "../upload/capture"
os.makedirs(snapshot_dir, exist_ok=True)

# Globals
current_frame = None
last_detections = []
last_saved_time = 0
lock = threading.Lock()
frame_skip = 2
min_area = 3000
save_interval = 5 # seconds
last_email_sent_time = 0
email_interval = 30  # seconds

# MySQL config
DB_HOST = 'localhost'
DB_USER = 'root'
DB_PASSWORD = ''
DB_NAME = 'protecting_agriculture_land_form_thef_animal'

# Runtime globals
detection_started = False
current_user_id = None
cap = None

def get_user_stream_link(user_id):
    try:
        conn = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME
        )
        cursor = conn.cursor()
        cursor.execute("SELECT stream_link FROM user_profile WHERE id = %s", (user_id,))
        result = cursor.fetchone()
        cursor.close()
        conn.close()

        if result and result[0]:
            return result[0]
        else:
            print(f"[ERROR] No stream link found for user_id {user_id}")
            return None
    except Exception as e:
        print(f"[DB ERROR] Failed to fetch stream link: {e}")
        return None

def insert_captured_image_to_db(image_path):
    global current_user_id, last_email_sent_time

    if not current_user_id:
        print("[ERROR] No user_id set for this request")
        return

    table_name = f"captured_images_user_{current_user_id}"

    try:
        with open(image_path, "rb") as f:
            img_blob = f.read()

        conn = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME
        )
        cursor = conn.cursor()

        sql = f"INSERT INTO `{table_name}` (image_path, captured_image, captured_at) VALUES (%s, %s, NOW())"
        relative_path = os.path.relpath(image_path, start=os.path.dirname(snapshot_dir))
        cursor.execute(sql, (relative_path, img_blob))

        conn.commit()
        cursor.close()
        conn.close()
        print(f"[DB] Inserted image with blob into {table_name}: {relative_path}")

        now = time.time()
        if now - last_email_sent_time >= email_interval:
            try:
                php_url = "http://localhost/Web Project/Protecting-Agriculture-Land-From-Theft-and-Animal/src/php/Send_Image_Email.php"
                post_data = {"user_id": current_user_id}
                response = requests.post(php_url, data=post_data)
                print(f"[EMAIL] PHP Response: {response.text}")
                last_email_sent_time = now  
            except Exception as e:
                print(f"[EMAIL ERROR] {e}")
        else:
            print(f"[EMAIL] Skipped: Waiting {int(email_interval - (now - last_email_sent_time))}s before next email.")

    except mysql.connector.Error as err:
        print(f"[DB ERROR] Failed to insert image: {err}")
    except Exception as e:
        print(f"[ERROR] {e}")


def detect_objects():
    global current_frame, last_detections, last_saved_time, cap
    frame_count = 0

    while True:
        if cap is None:
            time.sleep(0.1)
            continue

        # Flush stale frames to reduce latency
        for _ in range(5):
            cap.grab()

        ret, frame = cap.read()
        if not ret:
            print("[WARN] Unable to read frame from stream")
            time.sleep(0.2)
            continue

        frame = cv2.resize(frame, (640, 480))
        clean_frame = frame.copy()
        frame_count += 1

        if frame_count % frame_skip == 0:
            results = model.predict(frame, conf=0.5, classes=[0, 15, 16], verbose=False)
            boxes = results[0].boxes
            last_detections.clear()

            if boxes is not None and len(boxes) > 0:
                for box in boxes:
                    x1, y1, x2, y2 = map(int, box.xyxy[0])
                    cls_id = int(box.cls[0])
                    label = LABELS.get(cls_id, "object")

                    # 🚨 Send alert to Raspberry Pi if specific objects are detected
                    if label in ["person", "dog", "cow"]:
                        try:
                            alert_url = "http://10.56.242.182:5000/alert"
                            requests.post(alert_url, data={"type": label})
                            print(f"[ALERT] Sent alert to Raspberry Pi for {label}")
                        except Exception as e:
                            print(f"[ALERT ERROR] Could not send alert: {e}")

                    area = (x2 - x1) * (y2 - y1)
                    if area < min_area:
                        continue

                    last_detections.append((x1, y1, x2, y2, label))

                if time.time() - last_saved_time > save_interval:
                    timestamp_for_file = datetime.now().strftime("%d%m%Y_%H%M%S")
                    filename = f"detected_{timestamp_for_file}.jpg"
                    latest_frame_path = os.path.join(snapshot_dir, filename)

                    timestamp_for_img = datetime.now().strftime("Date : %d/%m/%Y Time : %I:%M:%S %p TEAM DSY")
                    cv2.putText(clean_frame, timestamp_for_img, (10, clean_frame.shape[0] - 10),
                                cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)

                    cv2.imwrite(latest_frame_path, clean_frame)
                    last_saved_time = time.time()

                    insert_captured_image_to_db(latest_frame_path)

        for x1, y1, x2, y2, label in last_detections:
            cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)
            cv2.putText(frame, label, (x1, y1 - 10),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 255, 0), 2)

        with lock:
            current_frame = frame


def generate_stream():
    global current_frame
    encode_param = [int(cv2.IMWRITE_JPEG_QUALITY), 70]  # Faster encoding

    while True:
        with lock:
            if current_frame is None:
                continue
            ret, buffer = cv2.imencode('.jpg', current_frame, encode_param)
            frame = buffer.tobytes()

        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame + b'\r\n')


@app.route("/live")
def video_feed():
    global current_user_id, detection_started, cap

    try:
        user_id = int(request.args.get("user_id"))
    except:
        return "Missing or invalid user_id", 400

    stream_link = get_user_stream_link(user_id)
    if not stream_link:
        return f"No stream link for user {user_id}", 404

    current_user_id = user_id

    cap = cv2.VideoCapture(stream_link)
    cap.set(cv2.CAP_PROP_BUFFERSIZE, 1)  # Reduce latency

    if not cap.isOpened():
        return "Failed to open video stream", 500

    if not detection_started:
        threading.Thread(target=detect_objects, daemon=True).start()
        detection_started = True

    return Response(generate_stream(),
                    mimetype="multipart/x-mixed-replace; boundary=frame")


def fetch_latest_image_from_db(user_id):
    try:
        conn = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME
        )
        cursor = conn.cursor()
        table_name = f"captured_images_user_{user_id}"
        query = f"""
            SELECT captured_image, captured_at FROM `{table_name}`
            ORDER BY captured_at DESC LIMIT 1
        """
        cursor.execute(query)
        result = cursor.fetchone()
        cursor.close()
        conn.close()
        if result:
            return result[0], result[1]
        else:
            return None, None
    except Exception as e:
        print(f"[DB ERROR] {e}")
        return None, None


@app.route("/latest")
def get_latest_frame():
    user_id = request.args.get("user_id")
    if not user_id:
        return "[ERROR] No user_id set for this request", 400

    image_blob, captured_at = fetch_latest_image_from_db(user_id)
    if image_blob:
        return send_file(
            io.BytesIO(image_blob),
            mimetype='image/jpeg',
            as_attachment=False,
            download_name=f"latest_{user_id}.jpg"
        )
    else:
        return "No detection yet", 404


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5001, debug=False, threaded=True)
