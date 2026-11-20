const token = process.env.VERCEL_TOKEN;
const orgId = process.env.VERCEL_ORG_ID;
const projectId = process.env.VERCEL_PROJECT_ID;

for (const [name, value] of Object.entries({
    VERCEL_TOKEN: token,
    VERCEL_ORG_ID: orgId,
    VERCEL_PROJECT_ID: projectId,
})) {
    if (!value) throw new Error(`${name} is missing`);
}

async function verify(label, url) {
    const response = await fetch(url, {
        headers: { Authorization: `Bearer ${token}` },
    });
    if (response.ok) {
        console.log(`${label}: HTTP ${response.status}`);
        return true;
    }
    const payload = await response.json().catch(() => ({}));
    const code = payload.error?.code ?? 'unknown';
    console.error(`${label}: HTTP ${response.status}, code ${code}`);
    return false;
}

await verify('Vercel user', 'https://api.vercel.com/v2/user');
const projectAccessible = await verify(
    'OptiBus project access',
    `https://api.vercel.com/v9/projects/${encodeURIComponent(projectId)}?teamId=${encodeURIComponent(orgId)}`,
);
if (!projectAccessible) process.exitCode = 1;
