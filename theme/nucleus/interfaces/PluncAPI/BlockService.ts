import { PluncElementInterface } from "./PluncElement";

export interface BlockService {
    get<TElement extends Element>(
        elementName: string,
        callback: BlockCallback<TElement>
    ): void
}

export type BlockCallback<TElement extends Element> = (
    element: PluncElementInterface<TElement>
) => void;
