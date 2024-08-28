export default interface Config {
    version?:string,
    name?:string,
    img_url?:string,
    ws_url?:string,
    active?:string,
    tabActive?:string,
    tabOtcType?:string,
    tabOtcCid?:string,
    tab?:string,
    wxgzh_login?:boolean,
    language?:any,
    language_code?:string,
    language_name?:string,
    language_arr?:any,
    [propName:string]: any
}