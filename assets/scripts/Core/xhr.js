import { BASE_URL } from '../config';


export function xhrRequest(url, method, data, callback)
{
    const xhr = new XMLHttpRequest();
    xhr.open(method, BASE_URL+url, true);

    if (data !== null) {
        xhr.send(data);
    } else {
        xhr.send();
    }

    xhr.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            console.log(this.responseText);
            callback(JSON.parse(this.responseText));
        }
    };
}