import { AuthServiceProviderInterface } from "../../interfaces/Providers/AuthServiceProviderInterface";

export class UserService {
    
    constructor(
        private authServiceProvider: AuthServiceProviderInterface
    ) {}

}