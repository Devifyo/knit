import { onBeforeUnmount } from 'vue';

/**
 * Subscribe to a Laravel Echo (Reverb) channel and auto-leave on unmount.
 *
 * @example
 *   useEcho(`tenant.${tenantId}.notifications`, '.NotificationCreated', (e) => toast.success(e.message));
 */
export function useEcho(channelName, eventName, callback, { presence = false, privateChannel = true } = {}) {
    if (typeof window === 'undefined' || !window.Echo) return { leave() {} };

    let channel;
    if (presence) {
        channel = window.Echo.join(channelName);
    } else if (privateChannel) {
        channel = window.Echo.private(channelName);
    } else {
        channel = window.Echo.channel(channelName);
    }

    if (eventName && callback) {
        channel.listen(eventName, callback);
    }

    const leave = () => window.Echo.leave(channelName);
    onBeforeUnmount(leave);

    return { channel, leave };
}
