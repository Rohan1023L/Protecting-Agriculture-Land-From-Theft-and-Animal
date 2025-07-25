function showContent(section) {
  const contentArea = document.getElementById("content-area");

  let content = "";

  switch (section) {
    case 'intro':
      content = `
        <h2 style="text-align: center;">Project Introduction</h2>
        <p style="text-align: justify; padding-left: 20px; padding-right: 20px;" >
        The Agriculture Surveillance System is an innovative solution designed to enhance the security and productivity of agricultural fields by utilizing smart surveillance technology. 
        In many rural and semi-urban areas, crops are frequently damaged due to unauthorized human entry and animal intrusions, leading to significant losses for farmers.
        This project aims to address this issue by deploying cameras and intelligent detection systems capable of monitoring the farmland in real time.</p>

        <p style="text-align: justify; padding-left: 20px; padding-right: 20px;" >
        By integrating computer vision and motion detection technologies, the system can identify unauthorized persons or animals entering the premises and immediately alert the farmer through a notification system.
        The solution reduces manual monitoring efforts and improves the response time to potential threats. 
        This automation not only helps protect the crops but also contributes to the overall modernization of agriculture through the application of smart technologies.</p>
        <h3 style="text-align: center;">Features</h3>
        <p style="text-align: justify; padding-left: 20px; padding-right: 20px;" > 
        --> User authentication (Login/Register)<br>
        --> Live video streaming from farm<br>
        --> Real-time intruder and animal detection<br>
        --> Automatic image capture and alert<br>
        --> Voice message to scare off threats<br>
        --> Feedback and support section</p>
      `;
      break;

    case 'manual':
      content = `
        <h2>User Manual</h2>
        <p>This section explains how to use the system, set up cameras, configure alerts, and access the dashboard for live surveillance.</p>
      `;
      break;

    case 'faqs':
      content = `
        <h2>Frequently Asked Questions</h2>
        <ul>
          <li><strong>Q:</strong> How do I install the system?<br><strong>A:</strong> Refer to the user manual for step-by-step instructions.</li>
          <li><strong>Q:</strong> What happens during a power cut?<br><strong>A:</strong> The system has a battery backup feature.</li>
        </ul>
      `;
      break;

    case 'privacy':
      content = `
        <h2>Privacy and Security Help</h2>
        <p>Your data is encrypted and stored securely. Only authorized users can access live feeds and recordings. For more help, contact support.</p>
      `;
      break;

    case 'terms':
      content = `
        <h2>Terms and Policies</h2>
        <p>By using this system, you agree to our terms regarding data storage, user behavior, and system usage. Unauthorized tampering is strictly prohibited.</p>
      `;
      break;

    case 'feedback':
      content = `
        <h2>Feedback</h2>
        <p>We value your feedback! Please share your suggestions or issues via our feedback form to help us improve the system.</p>
      `;
      break;

    default:
      content = "<p>Section not found.</p>";
  }

  contentArea.innerHTML = content;
}
