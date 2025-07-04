const app = plunc.create('app');
app.component("xhSZ", (xUhZ, xRVm, xgTW) => {
    const TestBlock = xUhZ;
const UserFactory = xRVm.UserFactory;
const RouteService = xgTW;

        var TestComponent;
    (function (TestComponent) {
        TestComponent.render = () => {
            const testBlock = TestBlock.render();
            RouteService.makeRoute();
            const user = new UserFactory();
        };
    })(TestComponent || (TestComponent = {}));
    console.log("TestComponent loaded # 3");

        return {
        TestComponent: TestComponent
    }
});
app.helper("xUhZ", () => {
    
        var TestBlock;
    (function (TestBlock) {
        TestBlock.render = () => {
        };
    })(TestBlock || (TestBlock = {}));

        return {
        TestBlock: TestBlock
    }
});
app.factory("xRVm", () => {
    
        class UserFactory {
    }

        return UserFactory;
});
app.service("xgTW", () => {
    
        var RouteService;
    (function (RouteService) {
        RouteService.makeRoute = () => {
            console.log("RouteService.makeRoute called");
        };
    })(RouteService || (RouteService = {}));

        return {
        RouteService: RouteService
    }
});
app.component("xTba", () => {
    
        var TestComponent;
    (function (TestComponent) {
        console.log('AnotherTests/TestComponent/TestComponent.ts loaded');
    })(TestComponent || (TestComponent = {}));

        return {
        TestComponent: TestComponent
    }
});
