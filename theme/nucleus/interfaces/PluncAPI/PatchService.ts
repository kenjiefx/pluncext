export interface PatchService {

    patch: (elementName?: string) => Promise<void>

}