declare type Procedure = (...args: any[]) => any;
interface DebouncedFunction<F extends Procedure> {
    (this: ThisParameterType<F>, ...args: Parameters<F>): void;
    cancel: () => void;
}
/**
 * 一个简单的防抖函数
 * @param func 需要限制执行频率的函数
 * @param delay 延迟时间，这段时间过后，才可触发第二次
 * @returns void
 */
export declare function debounce<F extends Procedure>(func: F, delay: number): DebouncedFunction<F>;
export {};
