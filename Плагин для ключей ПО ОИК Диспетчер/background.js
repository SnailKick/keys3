chrome.runtime.onInstalled.addListener(function() {
    chrome.contextMenus.create({
        id: "addLicense",
        title: "Добавить лицензию в базу данных",
        contexts: ["selection"]
    });
});

chrome.contextMenus.onClicked.addListener(function(info, tab) {
    if (info.menuItemId === "addLicense") {
        chrome.storage.local.set({ selectedText: info.selectionText, pageUrl: info.pageUrl }, function() {
            chrome.tabs.create({ url: chrome.runtime.getURL("popup.html") });
        });
    }
});


// chrome.runtime.onMessage.addListener(function(request, sender, sendResponse) {
//     if (request.action === "showPopup") {
//         chrome.storage.local.set({ selectedText: request.text, pageUrl: sender.tab.url }, function() {
//             chrome.tabs.create({ url: chrome.runtime.getURL("popup.html") });
//         });
//     }
// });