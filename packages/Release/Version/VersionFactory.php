<?php

declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Release\Version;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Contract\Git\TagResolverInterface;
use Symplify\MonorepoBuilder\Release\Guard\ReleaseGuard;
use Symplify\MonorepoBuilder\Release\ValueObject\SemVersion;

final class VersionFactory
{
    public function __construct(
        private ReleaseGuard $releaseGuard,
        private TagResolverInterface $tagResolver
    ) {
    }

    public function createValidVersion(string $versionArgument, string $stage): Version
    {
        // normalize to workaround phar-io bug
        $versionArgument = strtolower($versionArgument);

        if (in_array($versionArgument, SemVersion::ALL, true)) {
            return $this->resolveNextVersionByVersionKind($versionArgument);
        }

        // this object performs validation of version
        $version = new Version($versionArgument);
        $this->releaseGuard->guardVersion($version, $stage);

        return $version;
    }

    private function resolveNextVersionByVersionKind(string $versionKind): Version
    {
        // get current version
        $mostRecentVersionString = $this->tagResolver->resolve(getcwd());
        if ($mostRecentVersionString === null) {
            // the very first tag
            return new Version('v0.1.0');
        }

        $mostRecentVersion = new Version($mostRecentVersionString);

        $value = $mostRecentVersion->getMajor()
            ->getValue();
        $currentMinorVersion = $mostRecentVersion->getMinor()
            ->getValue();
        $currentPatchVersion = $mostRecentVersion->getPatch()
            ->getValue();

        if ($versionKind === SemVersion::MAJOR) {
            ++$value;
            $currentMinorVersion = 0;
            $currentPatchVersion = 0;
        }

        if ($versionKind === SemVersion::MINOR) {
            ++$currentMinorVersion;
            $currentPatchVersion = 0;
        }

        if ($versionKind === SemVersion::PATCH) {
            ++$currentPatchVersion;
        }

        return new Version(sprintf('%d.%d.%d', $value, $currentMinorVersion, $currentPatchVersion));
    }
}
