import { useEffect, useRef, useState } from 'react';

/** The warning shows this long before the idle time is up. */
const WARNING_MS = 60_000;
const ACTIVITY_EVENTS = ['pointermove', 'pointerdown', 'keydown', 'scroll', 'touchstart'] as const;

/**
 * Counts the time since the person last did anything. In the last minute `secondsLeft` is the seconds to the end (null before that), and
 * at the end `onExpire` runs once. While the warning is open only `reset` counts: moving the mouse must not hide it.
 */
export function useIdleCountdown(idleMs: number, onExpire: () => void): { secondsLeft: number | null; reset: () => void } {
    // The clock starts on mount (lazy state keeps Date.now() out of render).
    const [mountedAt] = useState(() => Date.now());
    const lastActivity = useRef(mountedAt);
    const warning = useRef(false);
    const [secondsLeft, setSecondsLeft] = useState<number | null>(null);

    useEffect(() => {
        if (idleMs <= 0) {
            return;
        }

        const markActive = () => {
            if (!warning.current) {
                lastActivity.current = Date.now();
            }
        };

        for (const name of ACTIVITY_EVENTS) {
            window.addEventListener(name, markActive, { passive: true });
        }

        const tick = setInterval(() => {
            const left = idleMs - (Date.now() - lastActivity.current);

            if (left <= 0) {
                clearInterval(tick);
                onExpire();

                return;
            }

            warning.current = left <= WARNING_MS;
            setSecondsLeft(warning.current ? Math.ceil(left / 1000) : null);
        }, 1000);

        return () => {
            clearInterval(tick);

            for (const name of ACTIVITY_EVENTS) {
                window.removeEventListener(name, markActive);
            }
        };
    }, [idleMs, onExpire]);

    const reset = () => {
        lastActivity.current = Date.now();
        warning.current = false;
        setSecondsLeft(null);
    };

    return { secondsLeft, reset };
}
