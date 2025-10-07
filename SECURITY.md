# Security Policy

## Supported Versions

We take security seriously and will address security vulnerabilities promptly. The following versions are currently supported with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 0.1.x   | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability within Laravel API Scaffold, please send an email to iamgerwin@example.com. All security vulnerabilities will be promptly addressed.

**Please do not report security vulnerabilities through public GitHub issues.**

### What to Include

When reporting a vulnerability, please include:

- Type of vulnerability
- Full paths of source file(s) related to the vulnerability
- Location of the affected source code (tag/branch/commit or direct URL)
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the issue, including how an attacker might exploit it

### Response Timeline

- We will acknowledge receipt of your vulnerability report within 48 hours
- We will provide a detailed response within 7 days, including the next steps
- We will work on a fix and keep you informed of the progress
- Once fixed, we will release a security update and publicly credit you (unless you prefer to remain anonymous)

## Security Best Practices

When using Laravel API Scaffold, follow these security best practices:

1. **Keep Dependencies Updated**: Regularly update the package and its dependencies
2. **Review Generated Code**: Always review generated code before deploying to production
3. **Validate Input**: Add proper validation rules to generated Request classes
4. **Authorize Requests**: Implement proper authorization in generated controllers
5. **Sanitize Output**: Ensure API Resources properly sanitize sensitive data
6. **Use HTTPS**: Always use HTTPS in production environments
7. **Rate Limiting**: Implement rate limiting on API endpoints
8. **Authentication**: Add authentication to protect API routes

## Known Security Considerations

### Automatic Service Registration

The package automatically modifies `AppServiceProvider.php` to register service bindings. While this is safe, we recommend:

- Reviewing changes to your service provider
- Using version control to track modifications
- Enabling backups in the configuration (`backup_existing => true`)

### File Generation

The package generates files based on user input. To prevent issues:

- Validate service names before generation
- Use the `--force` flag cautiously in production environments
- Review generated files before committing to version control

### Stub Templates

Custom stub templates can introduce security risks if not properly vetted:

- Only use stubs from trusted sources
- Review custom stubs for potential injection vulnerabilities
- Keep stub templates in version control

## Security Updates

Security updates will be released as patch versions (e.g., 0.1.1) and will be announced:

- In the CHANGELOG.md file
- In GitHub releases
- Through GitHub security advisories

## Credits

We appreciate security researchers who responsibly disclose vulnerabilities. Contributors will be credited in security advisories and release notes (unless they prefer anonymity).
