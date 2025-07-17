from flask import Flask, Response
import cv2
from ultralytics import YOLO

app = Flask(__name__)

# Open camera (0 = default camera)
cap = cv2.VideoCapture(0)

# Load YOLOv8n model (lightweight, will auto-download if not found)
model = YOLO('yolov8n.pt')

def generate_frames():
    while True:
        success, frame = cap.read()
        if not success:
            break

        # Perform object detection
        results = model(frame)[0]
        annotated_frame = results.plot()  # Draw boxes

        # Encode as JPEG
        ret, buffer = cv2.imencode('.jpg', annotated_frame)
        frame_bytes = buffer.tobytes()

        # Yield in MJPEG format
        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame_bytes + b'\r\n')

@app.route('/')
def index():
    # This HTML page displays the video stream
    return '''
    <html>
    <head>
        <title>Live Stream</title>
    </head>
    <body style="text-align:center;">
        <h2>Live Object Detection Stream</h2>
        <img src="/video_feed" width="640" height="480" />
    </body>
    </html>
    '''

@app.route('/video_feed')
def video_feed():
    return Response(generate_frames(),
                    mimetype='multipart/x-mixed-replace; boundary=frame')

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
