export interface LoginServiceInterface {

    /**
     * @description
     * This method should return the current user logged in to the app.
     * If there is no user logged in, it should throw an error.
     */
    active(): Promise<any>;

    /**
     * @description
     * This method should redirect the user to the login page.
     */
    redirectToLogin(): void;

    /**
     * @description
     * This method should check if the user is authenticated.
     */
    isAuthenticated(): Promise<boolean>;

}