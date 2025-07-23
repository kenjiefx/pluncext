import { PluncElementInterface } from "./PluncElement"

export interface ComponentReflection {
    id: string
    name: string
    alias: string
    element: () => PluncElementInterface<HTMLDivElement>
}