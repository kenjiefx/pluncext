export const Pluncx = {
    scope: <T extends {[key: string]: any}>(): T => {
        return {} as T
    },
    app: () => {
        return {
            /**
             * Registers a function that executes when the App is ready
             * @param callback - Function to call after the app is set to ready
             */
            ready:(callback:()=>unknown)=> {}
        }
    },
    patch: (elementName?: string): Promise<void> => {
        return new Promise((resolve, reject) => resolve())
    },
    block: <TElement extends Element>(
        elementName: string,
        callback: BlockCallback<TElement>
    ) => {

    },
    AppService: {
        bootstrap: (): Promise<void> => {
            return new Promise((resolve, reject) => resolve()) 
        }
    },
    component: () => {
        return {
            id: '',
            name: '',
            alias: '',
            element: (): PluncElementInterface<HTMLDivElement> => {
                // @ts-expect-error
                return null
            }
        }
    }
}

/** Block API requires call back function */
export type BlockCallback<TElement extends Element> = (
    element: PluncElementInterface<TElement>
) => void;

export type BlockAPI = <TElement extends Element>(
    elementName: string,
    callback: BlockCallback<TElement>
) => void

export type PatchAPI = (elementName?: string) => Promise<void>


export interface PluncElementInterface<TElement extends Element> {
    /**
     * A reference to the element itself.
     * (Shouldn't be minified, as publicly-accessible)
     */
    $element: TElement;
    /**
     * A reference to parent element, wrapped in this `PluncElement` object
     * (Shouldn't be minified, as publicly-accessible)
     */
    $parent: PluncElementInterface<TElement>;
    /** Retrieves the $element */
    get(): TElement;
    /** Retrieves the state */
    getState(): string | null;
    /** Sets the state */
    setState(state: string): void;
    /** Adds a class */
    addClass(className: string): void;
    /** List existing classes */
    listClass(): Array<string>;
    /** Removes a class */
    removeClass(className: string): void;
    /** Toggle class names */
    toggleClass(className: string): void;
}