# Contributing to Laravel API Scaffold

Thank you for considering contributing to Laravel API Scaffold! This document outlines the process for contributing to this project.

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code. Please be respectful and constructive in all interactions.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When creating a bug report, include as many details as possible:

- **Use a clear and descriptive title**
- **Describe the exact steps to reproduce the problem**
- **Provide specific examples to demonstrate the steps**
- **Describe the behavior you observed and what behavior you expected**
- **Include screenshots if applicable**
- **Provide your environment details** (PHP version, Laravel version, OS, etc.)

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, include:

- **Use a clear and descriptive title**
- **Provide a detailed description of the suggested enhancement**
- **Explain why this enhancement would be useful**
- **List any examples of where this enhancement would be valuable**

### Pull Requests

1. **Fork the repository** and create your branch from `main`
2. **Follow the coding standards** - run Laravel Pint before committing
3. **Write tests** for any new functionality
4. **Ensure all tests pass** - run the test suite locally
5. **Update documentation** if you're changing functionality
6. **Write clear commit messages** following conventional commits

## Development Setup

1. Fork and clone the repository:
```bash
git clone https://github.com/your-username/laravel-api-scaffold.git
cd laravel-api-scaffold
```

2. Install dependencies:
```bash
composer install
```

3. Run the test suite:
```bash
composer test
```

4. Run code quality tools:
```bash
composer format
composer analyse
```

## Coding Standards

This project follows PSR-12 coding standards and uses Laravel Pint for code formatting.

### Running Laravel Pint

Format your code before committing:
```bash
composer format
```

Check code style without fixing:
```bash
vendor/bin/pint --test
```

### Running PHPStan

Ensure your code passes static analysis:
```bash
composer analyse
```

## Testing Guidelines

- Write tests for all new features and bug fixes
- Ensure tests are clear and well-documented
- Run the full test suite before submitting a PR
- Aim for high test coverage

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run specific test file
vendor/bin/pest tests/Feature/MakeServiceCommandTest.php
```

## Commit Message Guidelines

Follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, missing semicolons, etc.)
- `refactor`: Code changes that neither fix bugs nor add features
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Changes to build process or auxiliary tools

### Examples

```
feat(command): add support for nested service structures

Added ability to generate services in nested directories
using slash notation (e.g., Blog/Post)

Closes #123
```

```
fix(stubs): correct namespace in controller stub

Fixed incorrect namespace generation when using custom
controller namespace configuration

Fixes #456
```

## Pull Request Process

1. **Update documentation** for any changed functionality
2. **Add tests** covering your changes
3. **Ensure all tests pass** and code quality checks succeed
4. **Update the CHANGELOG.md** with your changes
5. **Create a pull request** with a clear title and description
6. **Link related issues** in the PR description
7. **Wait for review** - maintainers will review your PR

### PR Checklist

- [ ] Code follows PSR-12 standards (run `composer format`)
- [ ] All tests pass (run `composer test`)
- [ ] PHPStan analysis passes (run `composer analyse`)
- [ ] Documentation has been updated
- [ ] CHANGELOG.md has been updated
- [ ] Commit messages follow conventional commits
- [ ] New features have tests
- [ ] Bug fixes have tests

## Development Workflow

1. Create a new branch for your feature or fix:
```bash
git checkout -b feat/my-new-feature
# or
git checkout -b fix/bug-description
```

2. Make your changes and commit:
```bash
git add .
git commit -m "feat(scope): description"
```

3. Push to your fork:
```bash
git push origin feat/my-new-feature
```

4. Open a pull request on GitHub

## Questions?

If you have questions about contributing, feel free to:
- Open an issue for discussion
- Check existing issues and pull requests
- Review the documentation

## Recognition

Contributors will be recognized in the project's README and release notes. Thank you for helping make Laravel API Scaffold better!
