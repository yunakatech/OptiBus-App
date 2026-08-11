export const resolveAvatarUrl = (avatar?: string | null, name?: string): string => {
    if (typeof avatar === 'string' && avatar.trim() !== '') {
        return avatar;
    }

    const svg = `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 96 96'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0%' stop-color='#0891b2'/><stop offset='100%' stop-color='#0f172a'/></linearGradient></defs><rect width='96' height='96' rx='20' fill='url(#g)'/><circle cx='48' cy='35' r='15' fill='rgba(255,255,255,0.78)'/><path d='M19 82c3-16 15-25 29-25s26 9 29 25' fill='rgba(255,255,255,0.78)'/></svg>`;

    return `data:image/svg+xml;utf8,${encodeURIComponent(svg)}`;
};
