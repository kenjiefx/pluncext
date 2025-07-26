export type ComponentScope<T extends {[key:string]: any}> = T & {
    state: 'empty' | 'loading' | 'loaded' | 'error' | 'active' | 'inactive';
}