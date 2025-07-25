import { ProfileCard } from "../components/ProfileCard/ProfileCard";
import { AppContainer } from "../interfaces/AppContainer";
import { AuthServiceProviderInterface } from "../interfaces/Providers/AuthServiceProviderInterface";
import { AuthService } from "../services/Requesters/AuthService";

class App {
    constructor(
        private profileCard: ProfileCard
    ) {}

    async bootstrap() {

    }
}