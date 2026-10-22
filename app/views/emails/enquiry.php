<?php
/** Notification email for a new enquiry. Email clients need inline styles and tables. @var array $r @var array $fields @var ?string $adminUrl */
$font = "font-family:Montserrat,'Segoe UI',Arial,sans-serif;";
?><!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>New website enquiry</title></head>
<body style="margin:0;padding:0;background:#f6f5f0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f5f0;padding:24px 12px;">
<tr><td align="center">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border:1px solid #e7e6e0;border-radius:10px;overflow:hidden;">
        <tr><td style="background:#282929;padding:22px 28px;border-bottom:4px solid #fcbd11;">
            <p style="margin:0;<?= $font ?>font-size:12px;font-weight:600;color:#fcbd11;">Eventzpro website</p>
            <p style="margin:4px 0 0;<?= $font ?>font-size:20px;font-weight:700;color:#ffffff;">New enquiry from <?= e($r['name']) ?></p>
        </td></tr>
        <tr><td style="padding:24px 28px 8px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="<?= $font ?>font-size:14px;color:#2a2c2c;">
                <?php foreach ($fields as $label => $value): ?>
                    <tr>
                        <td style="padding:7px 0;width:130px;color:#5d6363;vertical-align:top;border-bottom:1px solid #f0efea;"><?= e($label) ?></td>
                        <td style="padding:7px 0;font-weight:600;vertical-align:top;border-bottom:1px solid #f0efea;">
                            <?php if ($label === 'Email'): ?><a href="mailto:<?= e($value) ?>" style="color:#282929;"><?= e($value) ?></a><?php else: ?><?= e($value) ?><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </td></tr>
        <tr><td style="padding:16px 28px 8px;">
            <p style="margin:0 0 8px;<?= $font ?>font-size:12px;font-weight:700;color:#a87800;">Message</p>
            <div style="<?= $font ?>font-size:14px;line-height:1.65;color:#2a2c2c;background:#fff9e8;border-left:3px solid #fcbd11;padding:14px 16px;border-radius:4px;"><?= nl2br(e($r['message'])) ?></div>
        </td></tr>
        <tr><td style="padding:20px 28px 28px;">
            <a href="mailto:<?= e($r['email']) ?>?subject=<?= rawurlencode('Re: ' . $r['topic']) ?>" style="display:inline-block;<?= $font ?>font-size:14px;font-weight:700;color:#282929;background:#fcbd11;padding:12px 20px;border-radius:8px;text-decoration:none;">Reply to <?= e(strtok($r['name'], ' ')) ?></a>
            <?php if ($adminUrl): ?>
                <a href="<?= e($adminUrl) ?>" style="display:inline-block;margin-left:8px;<?= $font ?>font-size:14px;font-weight:600;color:#282929;padding:12px 4px;text-decoration:underline;">Open in admin panel</a>
            <?php endif; ?>
        </td></tr>
    </table>
    <p style="margin:16px 0 0;<?= $font ?>font-size:12px;color:#8f9493;">Sent automatically by the contact form on eventzproconsult.com</p>
</td></tr>
</table>
</body>
</html>
