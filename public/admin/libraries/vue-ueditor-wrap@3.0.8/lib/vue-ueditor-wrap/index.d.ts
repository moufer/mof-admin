declare const VueUeditorWrap: import("../utils").WithInstall<import("vue").DefineComponent<{
    editorId: StringConstructor;
    name: StringConstructor;
    modelValue: {
        type: StringConstructor;
        default: string;
    };
    config: import("vue").PropType<UEDITOR_CONFIG>;
    mode: {
        type: import("vue").PropType<import("./VueUeditorWrap").ModeType>;
        default: string;
        validator: (value: string) => boolean;
    };
    observerOptions: {
        type: import("vue").PropType<MutationObserverInit>;
        default: () => {
            attributes: boolean;
            attributeFilter: string[];
            characterData: boolean;
            childList: boolean;
            subtree: boolean;
        };
    };
    observerDebounceTime: {
        type: NumberConstructor;
        default: number;
        validator: (value: number) => boolean;
    };
    forceInit: BooleanConstructor;
    destroy: {
        type: BooleanConstructor;
        default: boolean;
    };
    editorDependencies: {
        type: import("vue").PropType<string[]>;
    };
    editorDependenciesChecker: {
        type: import("vue").PropType<() => boolean>;
    };
}, () => JSX.Element, unknown, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, ("update:modelValue" | "before-init" | "ready")[], "update:modelValue" | "before-init" | "ready", import("vue").VNodeProps & import("vue").AllowedComponentProps & import("vue").ComponentCustomProps, Readonly<{
    modelValue: string;
    mode: import("./VueUeditorWrap").ModeType;
    observerOptions: MutationObserverInit;
    observerDebounceTime: number;
    forceInit: boolean;
    destroy: boolean;
} & {
    name?: string | undefined;
    editorId?: string | undefined;
    config?: UEDITOR_CONFIG | undefined;
    editorDependencies?: string[] | undefined;
    editorDependenciesChecker?: (() => boolean) | undefined;
}>, {
    modelValue: string;
    mode: import("./VueUeditorWrap").ModeType;
    observerOptions: MutationObserverInit;
    observerDebounceTime: number;
    forceInit: boolean;
    destroy: boolean;
}>>;
export default VueUeditorWrap;
export { VueUeditorWrap };
export type { ModeType } from './VueUeditorWrap';
