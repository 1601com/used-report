
var requestCounter = 0;

var isRunning = false;

var requestQueue = [];

var maxRequest = 2;

/**
 * @param {{elementId: string, data: Array, url: string, token: string, maxRequest: number}} param
 */
function addRequest(param) {
    maxRequest = param.maxRequest;
    requestQueue.push(param);
    worker();
}

/**
 *
 */
function worker() {
    if (isRunning) {
        return;
    }

    isRunning = true;

    setTimeout(function () {
        for (let i = requestCounter; i < (requestCounter + maxRequest); i++) {
            if (!requestQueue[i]) {
                isRunning = false;
                return;
            }

            sendRequest(requestQueue[i]);
        }

        requestCounter = requestCounter + maxRequest;

        isRunning = false;

        worker();

    }, 1000);
}


/**
 * Ajax function
 * @param {{elementId: string, data: Array, url: string, token: string, maxRequest: number}} param
 */
function sendRequest(param) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', param.url, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function (event) {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById(param.elementId).innerHTML = xhr.response;
        }
    };

    xhr.send("data=" + JSON.stringify(param.data) + "&REQUEST_TOKEN=" + param.token);
}



