import { TestBlock } from "../../../blocks/TestBlock/TestBlock";
import { Pluncx } from "../../../interfaces/pluncx";

export namespace TestComponent {
    console.log('AnotherTests/TestComponent/TestComponent.ts loaded');
    Pluncx.scope().firstName = 'John'
    export const render = () => {
        TestBlock.render();
    }
}