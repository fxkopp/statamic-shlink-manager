export function truncate(str, len = 60) {
    return str && str.length > len ? str.substring(0, len) + '...' : str;
}
