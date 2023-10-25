<?php

namespace SchemaImmo\WebExpose;

enum BlockType: string
{
	case Custom = 'custom';

	case Building = 'building';
	case Image = 'image';
	case ImageTabs = 'image-tabs';
	case Nearby = 'nearby';

	case Video = 'video';

	case Scan = 'scan';
	case CameraFeed = 'camera-feed';
	case Summary = 'summary';
	case Text = 'text';

	case FeatureColumns = 'feature-columns';
	case Features = 'features';

	/**
	 * @return class-string<Block>
	 */
	public function getBlockClass(): string
	{
		switch ($this) {
			case self::Building:
				return Block\BuildingBlock::class;
			case self::Image:
				return Block\ImageBlock::class;
			case self::ImageTabs:
				return Block\ImageTabsBlock::class;
			case self::Nearby:
				return Block\NearbyBlock::class;
			case self::Summary:
				return Block\SummaryBlock::class;
			case self::Text:
				return Block\TextBlock::class;
			case self::Video:
				return Block\VideoBlock::class;
			case self::Scan:
				return Block\ScanBlock::class;
			case self::FeatureColumns:
				return Block\FeatureColumnsBlock::class;
			case self::CameraFeed:
				return Block\CameraFeedBlock::class;
			case self::Features:
				return Block\FeaturesBlock::class;
			default:
				return Block\CustomBlock::class;
		}
	}
}
