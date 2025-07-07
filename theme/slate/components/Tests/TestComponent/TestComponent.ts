import { TestBlock } from "../../../blocks/TestBlock/TestBlock"
import { UserFactory } from "../../../factories/UserFactory"
import { RouteService } from "../../../services/RouteService"

export namespace TestComponent {
    export const render = () => {
        const testBlock = TestBlock.render()
        RouteService.makeRoute()
        const user = new UserFactory()
    }
}

console.log("TestComponent loaded # 3")