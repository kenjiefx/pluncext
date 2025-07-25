export namespace AppContainer {
    export const bind = <T>(cls: new (...args: any[]) => T) => {
        return {
            to: () => {

            }
        }
    }

    export const get = <T>(cls: new (...args: any[]) => T): T => {
        return new cls();
    }
}