import { UserService } from "../Users/UserService";

export class PostService {

    constructor(
        public readonly userService: UserService
    ) {}

}