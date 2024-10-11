document.addEventListener('mouseup', function() {
  let selectedText = window.getSelection().toString().trim();
  if (selectedText.length === 16) {
      if (chrome.runtime.id) {
          chrome.runtime.sendMessage({ action: "showPopup", text: selectedText }, function(response) {
              if (chrome.runtime.lastError) {
                  console.error("Ошибка при отправке сообщения:", chrome.runtime.lastError);
              } else {
                  console.log("Сообщение успешно отправлено:", response);
              }
          });
      } else {
          console.error("Контекст расширения недействителен.");
      }
  }
});