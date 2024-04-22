export declare class LoadEvent {
    listeners: {
        [key: string]: {
            triggered: boolean;
            requested: boolean;
            cbs: Array<() => void>;
        };
    };
    constructor();
    on(eventName: string, callback: () => void): void;
    emit(eventName: string): void;
}
