export default async function getCroppedImageFile(imageURL, croppedAreaPixels, name) {
  // Load the original image
  const image = new Image();

  // Set crossorigin attribute to anonymous to avoid CORS issues
  image.crossOrigin = 'Anonymous';
  image.src = imageURL;

  await new Promise((resolve) => {
    image.onload = () => resolve(image);
  });

  // Create a canvas and context to draw the cropped image
  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d');

  // Set the canvas size to match the cropped area
  canvas.width = croppedAreaPixels.width;
  canvas.height = croppedAreaPixels.height;

  // Draw the cropped area on the canvas
  ctx.drawImage(image, croppedAreaPixels.x, croppedAreaPixels.y, croppedAreaPixels.width, croppedAreaPixels.height, 0, 0, croppedAreaPixels.width, croppedAreaPixels.height);

  // Convert the canvas content to a data URL
  const dataURL = canvas.toDataURL('image/png');

  // Convert data URL to Blob
  const blob = dataURLtoBlob(dataURL);

  // Create a File object from the Blob
  return new File([blob], name);
}

function dataURLtoBlob(dataURL) {
  const parts = dataURL.split(';base64,');
  const contentType = parts[0].split(':')[1];
  const raw = window.atob(parts[1]);
  const rawLength = raw.length;
  const uint8Array = new Uint8Array(rawLength);

  for (let i = 0; i < rawLength; ++i) {
    uint8Array[i] = raw.charCodeAt(i);
  }

  return new Blob([uint8Array], { type: contentType });
}
