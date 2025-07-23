import { UserFactory } from "../../factories/UserFactory";
import { BlockService } from "../../interfaces/PluncAPI/BlockService";
import { ComponentScope } from "../../interfaces/PluncAPI/ComponentScope";
import { PatchService } from "../../interfaces/PluncAPI/PatchService";
import { PostService } from "../../services/Posts/PostService";
import { UserService } from "../../services/Users/UserService";

type ProfileCardProps = {
    firstName: string
}

export class ProfileCard {

    constructor(
        private userService: UserService, 
        private postService: PostService,
        private props: ComponentScope<ProfileCardProps>,
        private userFactory: UserFactory,
        private blockService: BlockService,
        private patchService: PatchService
    ) {}

    async render() {
        this.blockService.get('/Block/', () => {

        })
        this.props.firstName = 'John'
        this.patchService.patch()
    }

    

}