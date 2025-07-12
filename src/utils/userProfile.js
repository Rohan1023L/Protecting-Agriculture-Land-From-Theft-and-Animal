const fileInput = document.querySelector('input[type="file"]');
const userProfileDiv = document.querySelector('.user-profile');

fileInput.addEventListener('change', function () {
  const file = this.files[0];

  if (file && file.type.startsWith('image/')) {
    const reader = new FileReader();

    reader.onload = function (e) {
      userProfileDiv.style.backgroundImage = `url('${e.target.result}')`;
      userProfileDiv.style.backgroundSize = '100% 100%';
      userProfileDiv.style.backgroundPosition = 'center';
      userProfileDiv.style.width = '100px';  // Adjust size as needed
      userProfileDiv.style.height = '110px'; // Adjust size as needed
    };

    reader.readAsDataURL(file);
  }
});