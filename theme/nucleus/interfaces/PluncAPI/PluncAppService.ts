export interface PluncAppService {

    /**
     * Registers a function that executes when the App is ready
     * @param callback - Function to call after the app is set to ready
     */
    ready:(callback:()=>Promise<void>)=> {}

}