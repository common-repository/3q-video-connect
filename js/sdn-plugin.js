/**
 * Created by 3Q GmbH
 */
function sdnResize(playerId) {
    document.getElementById(playerId).setAttribute('style', 'height'+document.getElementById(playerId).clientWidth/1.778);
}
function sdnPlayerBridge(playerId, event, data) {
    switch (event) {
        case "ready":
            sdnResize(playerId);
        break;
    }
}