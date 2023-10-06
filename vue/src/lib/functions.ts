export function pad(nm:number) {
    if (nm < 10) {
        return '0' + nm;
    }
    return nm;
}

export function is_valid(id:any) {
    if (!id) return false;
    if (id.id) return is_valid(id.id);
    if (parseInt(id) > 0) return true;
    return false;

}