import { PropType } from 'vue';
export declare type ModeType = 'observer' | 'listener';
declare const _default: import("vue").DefineComponent<{
    editorId: StringConstructor;
    name: StringConstructor;
    modelValue: {
        type: StringConstructor;
        default: string;
    };
    config: PropType<UEDITOR_CONFIG>;
    mode: {
        type: PropType<ModeType>;
        default: string;
        validator: (value: string) => boolean;
    };
    observerOptions: {
        type: PropType<MutationObserverInit>;
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
        type: PropType<string[]>;
    };
    editorDependenciesChecker: {
        type: PropType<() => boolean>;
    };
}, () => JSX.Element, unknown, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, ("update:modelValue" | "before-init" | "ready")[], "update:modelValue" | "before-init" | "ready", import("vue").VNodeProps & import("vue").AllowedComponentProps & import("vue").ComponentCustomProps, Readonly<{
    modelValue: string;
    mode: ModeType;
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
    mode: ModeType;
    observerOptions: MutationObserverInit;
    observerDebounceTime: number;
    forceInit: boolean;
    destroy: boolean;
}>;
export default _default;
