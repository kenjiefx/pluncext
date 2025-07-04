import { Pluncx } from "../../interfaces/pluncx"

export namespace TestBlock {
    export const name = "TestBlock";
    export const render = () => {
        console.log('TestBlock/TestBlock.ts loaded');
    }
    Pluncx.scope().lastName = "Doe"
}