const getAvatarInitials = (name?: string): string => {
    const safeName = (name ?? '').trim();

    if (!safeName) {
return 'U';
}

    const words = safeName.split(/\s+/).filter(Boolean);

    if (words.length === 1) {
return words[0].slice(0, 2).toUpperCase();
}

    return `${words[0][0] ?? ''}${words[1][0] ?? ''}`.toUpperCase();
};

export const resolveAvatarUrl = (avatar?: string | null, name?: string): string => {
    if (typeof avatar === 'string' && avatar.trim() !== '') {
        return avatar;
    }

    const initials = getAvatarInitials(name);
    const svg = `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 96 96'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0%' stop-color='#0891b2'/><stop offset='100%' stop-color='#0f172a'/></linearGradient></defs><rect width='96' height='96' rx='20' fill='url(#g)'/><circle cx='48' cy='38' r='16' fill='rgba(255,255,255,0.24)'/><path d='M20 82c2-14 14-24 28-24s26 10 28 24' fill='rgba(255,255,255,0.24)'/><text x='48' y='90' text-anchor='middle' fill='#fff' font-size='13' font-family='Arial, sans-serif' font-weight='700'>${initials}</text></svg>`;

    return `data:image/svg+xml;utf8,${encodeURIComponent(svg)}`;
};
