var controller = null;
import { useAuthStore } from '../stores/auth';
import lang from './lang.js';

export function abort_all_calls() {
    if(controller) {
        controller.abort();
        controller = null;
    }
}

function validateResponse() {
    return res => {
        return res.json().then(json => {
            if (!json || !json.success) {
                if (json.data && json.data.error) {
                    throw new Error(lang.ERROR_VALIDATION, {cause: json.data});
                }
                else {
                    throw new Error(lang.ERROR_NETWORK_GEN);
                }
            }
            return json;
        })
    };
}

function validFetch(path, pdata, options, headers, responseParser) {
    if(!controller) {
        controller = new AbortController();
    }
    const contentHeaders = Object.assign({
        "Accept": "application/json",
        "Content-Type": "application/json"} , headers);

    const auth = useAuthStore();

    const data = {
        path: path,
        nonce: auth ? auth.nonce : ''
    };
    if (pdata && Object.keys(pdata).length > 0) {
        data.model = pdata;
    } 

    const fetchOptions = Object.assign({}, {headers: contentHeaders}, options, {
        credentials: "same-origin",
        redirect: "manual",
        method: 'POST',
        signal: controller.signal,
        body: JSON.stringify(data)
    });

    return fetch(auth ? auth.baseUrl : '', fetchOptions)
        .then(responseParser())
        .catch(err => {
            if(err.name === "AbortError") {
                console.log('disregarding aborted call');
            }
            else {
                throw err;
            }
        });
}

function fetchJson(path, data={}, options = {}, headers = {}) {
    return validFetch(path, data, options, headers, validateResponse);
}

function attachmentResponse() {
    return res => {
        return res.blob().then((blob)=> {
            var file = window.URL.createObjectURL(blob);
            window.location.assign(file);
        });
    };
}

function fetchAttachment(path, data={}, options={}, headers={}) {
    return validFetch(path, data, options, headers, attachmentResponse);
}

export function configuration() {
    return fetchJson('configuration');
}

export function saveconfiguration(data) {
    return fetchJson('configuration/save', data);
}

export function players() {
    return fetchJson('player');
}

export function savePlayer(player) {
    return fetchJson('player/save', player);
}

export function removePlayer(player) {
    return fetchJson('player/delete', player);
}

export function matches() {
    return fetchJson('match');
}

export function saveMatch(match) {
    return fetchJson('match/save', match);
}

export function removeMatch(match) {
    return fetchJson('match/delete', match);
}

export function reassessMatches() {
    return fetchJson('match/reassess');
}