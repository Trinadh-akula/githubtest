import { Transform } from './Util.js';
import { Node } from './Node.js';
import { Container } from './Container.js';
import { Stage } from './Stage.js';
import { Layer } from './Layer.js';
import { FastLayer } from './FastLayer.js';
import { Group } from './Group.js';
import { Shape } from './Shape.js';
import { Animation } from './Animation.js';
import { Tween } from './Tween.js';
import { Context } from './Context.js';
import { Canvas } from './Canvas.js';
export declare const Konva: {
    _global: any;
    version: string;
    isBrowser: boolean;
    isUnminified: boolean;
    dblClickWindow: number;
    getAngle(angle: number): number;
    enableTrace: boolean;
    pointerEventsEnabled: boolean;
    autoDrawEnabled: boolean;
    hitOnDragEnabled: boolean;
    capturePointerEventsEnabled: boolean;
    _mouseListenClick: boolean;
    _touchListenClick: boolean;
    _pointerListenClick: boolean;
    _mouseInDblClickWindow: boolean;
    _touchInDblClickWindow: boolean;
    _pointerInDblClickWindow: boolean;
    _mouseDblClickPointerId: null;
    _touchDblClickPointerId: null;
    _pointerDblClickPointerId: null;
    _fixTextRendering: boolean;
    pixelRatio: number;
    dragDistance: number;
    angleDeg: boolean;
    showWarnings: boolean;
    dragButtons: number[];
    isDragging(): any;
    isTransforming(): any;
    isDragReady(): boolean;
    releaseCanvasOnDestroy: boolean;
    document: any;
    _injectGlobal(Konva: any): void;
} & {
    Util: {
        _isElement(obj: any): obj is Element;
        _isFunction(obj: any): boolean;
        _isPlainObject(obj: any): boolean;
        _isArray(obj: any): obj is Array<any>;
        _isNumber(obj: any): obj is number;
        _isString(obj: any): obj is string;
        _isBoolean(obj: any): obj is boolean;
        isObject(val: any): val is object;
        isValidSelector(selector: any): boolean;
        _sign(number: number): 1 | -1;
        requestAnimFrame(callback: Function): void;
        createCanvasElement(): HTMLCanvasElement;
        createImageElement(): HTMLImageElement;
        _isInDocument(el: any): boolean;
        _urlToImage(url: string, callback: Function): void;
        _rgbToHex(r: number, g: number, b: number): string;
        _hexToRgb(hex: string): import("./types.js").RGB;
        getRandomColor(): string;
        getRGB(color: string): import("./types.js").RGB;
        colorToRGBA(str: string): {
            r: number;
            g: number;
            b: number;
            a: number;
        } | undefined;
        _namedColorToRBA(str: string): {
            r: number;
            g: number;
            b: number;
            a: number;
        } | null;
        _rgbColorToRGBA(str: string): {
            r: number;
            g: number;
            b: number;
            a: number;
        } | undefined;
        _rgbaColorToRGBA(str: string): {
            r: number;
            g: number;
            b: number;
            a: number;
        } | undefined;
        _hex8ColorToRGBA(str: string): {
            r: number;
            g: number;
            b: number;
            a: number;
        } | undefined;
        _hex6ColorToRGBA(str: string): {
            r: number;
            g: number;
            b: number;
            a: number;
        } | undefined;
        _hex4ColorToRGBA(str: string): {
            r: number;
            g: number;
            b: number;
            a: number;
        } | undefined;
        _hex3ColorToRGBA(str: string): {
            r: number;
            g: number;
            b: number;
            a: number;
        } | undefined;
        _hslColorToRGBA(str: string): {
            r: number;
            g: number;
            b: number;
            a: number;
        } | undefined;
        haveIntersection(r1: import("./types").IRect, r2: import("./types.js").IRect): boolean;
        cloneObject<Any>(obj: Any): Any;
        cloneArray(arr: Array<any>): any[];
        degToRad(deg: number): number;
        radToDeg(rad: number): number;
        _degToRad(deg: number): number;
        _radToDeg(rad: number): number;
        _getRotation(radians: number): number;
        _capitalize(str: string): string;
        throw(str: string): never;
        error(str: string): void;
        warn(str: string): void;
        each(obj: object, func: Function): void;
        _inRange(val: number, left: number, right: number): boolean;
        _getProjectionToSegment(x1: any, y1: any, x2: any, y2: any, x3: any, y3: any): any[];
        _getProjectionToLine(pt: import("./types").Vector2d, line: Array<import("./types").Vector2d>, isClosed: boolean): import("./types.js").Vector2d;
        _prepareArrayForTween(startArray: any, endArray: any, isClosed: any): number[];
        _prepareToStringify<T>(obj: any): T | null;
        _assign<T, U>(target: T, source: U): T & U;
        _getFirstPointerId(evt: any): any;
        releaseCanvas(...canvases: HTMLCanvasElement[]): void;
        drawRoundedRectPath(context: Context, width: number, height: number, cornerRadius: number | number[]): void;
    };
    Transform: typeof Transform;
    Node: typeof Node;
    Container: typeof Container;
    Stage: typeof Stage;
    stages: Stage[];
    Layer: typeof Layer;
    FastLayer: typeof FastLayer;
    Group: typeof Group;
    DD: {
        readonly isDragging: boolean;
        justDragged: boolean;
        readonly node: Node<import("./Node.js").NodeConfig> | undefined;
        _dragElements: Map<number, {
            node: Node;
            startPointerPos: import("./types.js").Vector2d;
            offset: import("./types.js").Vector2d;
            pointerId?: number;
            dragStatus: "ready" | "dragging" | "stopped";
        }>;
        _drag(evt: any): void;
        _endDragBefore(evt?: any): void;
        _endDragAfter(evt: any): void;
    };
    Shape: typeof Shape;
    shapes: {
        [key: string]: Shape<import("./Shape.js").ShapeConfig>;
    };
    Animation: typeof Animation;
    Tween: typeof Tween;
    Easings: {
        BackEaseIn(t: any, b: any, c: any, d: any): any;
        BackEaseOut(t: any, b: any, c: any, d: any): any;
        BackEaseInOut(t: any, b: any, c: any, d: any): any;
        ElasticEaseIn(t: any, b: any, c: any, d: any, a: any, p: any): any;
        ElasticEaseOut(t: any, b: any, c: any, d: any, a: any, p: any): any;
        ElasticEaseInOut(t: any, b: any, c: any, d: any, a: any, p: any): any;
        BounceEaseOut(t: any, b: any, c: any, d: any): any;
        BounceEaseIn(t: any, b: any, c: any, d: any): any;
        BounceEaseInOut(t: any, b: any, c: any, d: any): any;
        EaseIn(t: any, b: any, c: any, d: any): any;
        EaseOut(t: any, b: any, c: any, d: any): any;
        EaseInOut(t: any, b: any, c: any, d: any): any;
        StrongEaseIn(t: any, b: any, c: any, d: any): any;
        StrongEaseOut(t: any, b: any, c: any, d: any): any;
        StrongEaseInOut(t: any, b: any, c: any, d: any): any;
        Linear(t: any, b: any, c: any, d: any): any;
    };
    Context: typeof Context;
    Canvas: typeof Canvas;
};
export default Konva;
