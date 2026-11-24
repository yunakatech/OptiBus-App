import fs from 'node:fs';

const result = JSON.parse(fs.readFileSync(process.argv[2], 'utf8'));
const id = result.deployment?.id ?? result.id;

if (!/^dpl_[A-Za-z0-9]+$/.test(id ?? '')) {
    throw new Error('Vercel deploy did not return a deployment ID');
}

const token = process.env.VERCEL_TOKEN;
const teamId = process.env.VERCEL_ORG_ID;
const projectId = process.env.VERCEL_PROJECT_ID;

if (!token || !teamId || !projectId) {
throw new Error('Vercel credentials are missing');
}

for (let attempt = 0; attempt < 120; attempt++) {
    const response = await fetch(
        `https://api.vercel.com/v13/deployments/${id}?teamId=${encodeURIComponent(teamId)}`,
        { headers: { Authorization: `Bearer ${token}` } },
    );

    if (!response.ok) {
throw new Error(`Deployment lookup returned HTTP ${response.status}`);
}

    const deployment = await response.json();

    if (
        (deployment.projectId && deployment.projectId !== projectId) ||
        deployment.name !== 'optibus-app' ||
        deployment.target !== 'production'
    ) {
        throw new Error('Deployment belongs to a different project or target');
    }

    if (deployment.readyState === 'READY') {
        fs.appendFileSync(
            process.env.GITHUB_OUTPUT,
            `id=${id}\nurl=https://${deployment.url}\n`,
        );
        console.log(`Deployment ${id} is Ready`);
        process.exit(0);
    }

    if (['ERROR', 'CANCELED'].includes(deployment.readyState)) {
        throw new Error(`Deployment ${id} ended in ${deployment.readyState}`);
    }

    await new Promise((resolve) => setTimeout(resolve, 5000));
}

throw new Error(`Deployment ${id} did not become Ready within ten minutes`);
