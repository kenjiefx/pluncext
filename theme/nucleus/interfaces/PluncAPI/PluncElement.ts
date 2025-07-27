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