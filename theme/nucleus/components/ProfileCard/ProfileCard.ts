import { CardHeaderBlock } from "../../blocks/CardHeaderBlock/CardHeaderBlock";
import { UserFactory } from "../../factories/UserFactory";
import { BlockService } from "../../interfaces/PluncAPI/BlockService";
import { ComponentReflection } from "../../interfaces/PluncAPI/ComponentReflection";
import { ComponentScope } from "../../interfaces/PluncAPI/ComponentScope";
import { PatchService } from "../../interfaces/PluncAPI/PatchService";
import { PostService } from "../../services/Posts/PostService";
import { UserService } from "../../services/Users/UserService";

type ProfileCardProps = {
    id: string
    firstName: string
}

export class ProfileCard {

    constructor(
        private userService: UserService, 
        private postService: PostService,
        private props: ComponentScope<ProfileCardProps>,
        private userFactory: UserFactory,
        private blockService: BlockService,
        private patchService: PatchService,
        private cardHeaderBlock: CardHeaderBlock,
        private componentReflector: ComponentReflection
    ) {
    }
    
    async render() {
        this.props.state = 'active';
        this.props.firstName = 'Ryan';
        this.props.id = this.componentReflector.id
        await this.patchService.patch()
        this.cardHeaderBlock.renderHeader()
    }

}

