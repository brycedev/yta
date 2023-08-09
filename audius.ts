import dotenv from "dotenv";
dotenv.config();

import {
    AppAuth,
    DiscoveryNodeSelector,
    EntityManager,
    StorageNodeSelector,
    UploadTrackRequest,
    sdk,
    stagingConfig,
    Genre,
} from "@audius/sdk";
import { SdkConfig } from "@audius/sdk/dist/sdk/types";

import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const apiKey = process.env.AUDIUS_API_KEY || "";
const apiSecret = process.env.AUDIUS_API_SECRET || "";
const audiusEnv = process.env.AUDIUS_ENV || "staging";

let appName = "AudiusCast_Staging";
if (audiusEnv === "production") {
    appName = "AudiusCast";
}

const userId = process.argv[3];
const data = JSON.parse(process.argv[5]);
const audioPath = process.argv[7];
const imagePath = process.argv[9];

const discoveryNodeSelector = new DiscoveryNodeSelector({
    initialSelectedNode: "https://discoveryprovider.staging.audius.co/",
});

const stagingEntityManager = new EntityManager({
    discoveryNodeSelector,
    web3ProviderUrl: stagingConfig.web3ProviderUrl,
    contractAddress: stagingConfig.entityManagerContractAddress,
    identityServiceUrl: stagingConfig.identityServiceUrl,
});

const stagingStorageNodeSelector = new StorageNodeSelector({
    discoveryNodeSelector,
    auth: new AppAuth(apiKey, apiSecret),
    bootstrapNodes: [
        {
            delegateOwnerWallet: "0x671ddce7B4E676C9467F87e4031a917b5D6f75F0",
            endpoint: "https://usermetadata.staging.audius.co",
        },
    ],
});

const stagingServices = {
    discoveryNodeSelector,
    entityManager: stagingEntityManager,
    storageNodeSelector: stagingStorageNodeSelector,
};

const sdkConfig: SdkConfig = {
    appName: appName,
    apiKey: apiKey,
    apiSecret: apiSecret,
};

if (audiusEnv === "staging") {
    sdkConfig.services = stagingServices;
}

const audiusSdk = sdk(sdkConfig);

try {
    const uploadTrackRequest: UploadTrackRequest = {
        userId: userId,
        coverArtFile: {
            buffer: imagePath
                ? fs.readFileSync(imagePath)
                : fs.readFileSync(path.join(__dirname, "default.jpg")),
            name: "my cover art",
        },
        metadata: {
            description: data.description
                ? data.description.substring(0, 1000)
                : "",
            title: data.title || "",
            genre: Genre.PODCASTS,
            // tags: data.tags,
            releaseDate: new Date(data.date),
        },
        onProgress: (progress) => console.log("Progress:", progress),
        trackFile: {
            buffer: fs.readFileSync(audioPath),
            name: "my track file",
        },
    };
    const result = await audiusSdk.tracks.uploadTrack(uploadTrackRequest);

    const track = await audiusSdk.tracks.getTrack({
        trackId: result.trackId.toString(),
    });

    console.log(track.data?.permalink);

    process.exit(0);
} catch (error) {
    console.log(error);
    process.exit(1);
}
